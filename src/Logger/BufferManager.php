<?php

namespace Craftix\Logger;

use Swoole\Coroutine;
use Swoole\Coroutine\Channel;
use Throwable;
use const STDOUT;

class BufferManager
{
    /** Estimated average bytes per encoded log line (used for capacity) */
    protected int $avgLineBytes = 320;

    /** Hard memory limit for in‑memory buffer */
    protected int $maxBufferBytes = 32 * 1024 * 1024;

    /** Non‑blocking push timeout (seconds) */
    protected float $pushTimeout = 0.001;

    /** Read or pop from logs buffer timeout */
    protected float $bufferQueuePopTimeout = 10;

    /** Channel holding encoded log lines waiting to be flushed. */
    public readonly Channel $buffer;

    /** Singleton instance per PHP worker. */
    private static ?self $instance = null;

    /** Console logs buffer reader timer id */
    protected int $bufferReaderTimerId;

    /** Console buffer instance is in closing status */
    protected  bool $isClosing = false;

    /** Create an instance of console buffer manager . Calculate buffer capacity from memory budget */
    private function __construct()
    {
        $capacity = max(1, intdiv($this->maxBufferBytes, $this->avgLineBytes));
        $this->buffer = new Channel($capacity);
        $this->initBufferReaderTimer();
    }

    /** Background coroutine to drain the buffer and print to STDOUT */
    public function initBufferReaderTimer(): void
    {
        $this->bufferReaderTimerId = Coroutine::create(function () {
            while (!$this->isClosing) {
                $logStream = $this->buffer->pop($this->bufferQueuePopTimeout);

                ## Check pop timeout reached
                if ($this->buffer->errCode === SWOOLE_CHANNEL_CLOSED) {
                    $this->error("Console logger buffer channel closed unexpectedly");
                    break;
                }
                else if ($logStream instanceof BufferedLog) {
                    $this->onBufferLogReceive($logStream);
                } else {
                   ## Do nothing in channel timeout
                }
            }
        });
    }

    /** Print log stream with log printer driver */
    public function onBufferLogReceive(BufferedLog $bufferedLog): void
    {
        Logger::onLogReceived($bufferedLog);
    }

    /** Create an instance of console logs buffer manager */
    public static function make(): self
    {
        return self::$instance ??= new self();
    }

    /** Enqueue a pre‑formatted log line. Line is encoded log line including newline */
    public function push(BufferedLog $bufferedLog): bool
    {
        return $this->buffer->push($bufferedLog, $this->pushTimeout);
    }

    /** Internal error happen  */
    public function error(string $message): void
    {
        error_log($message.PHP_EOL);
    }

    /** Destruct console logs buffer manager instance and its resources */
    public function __destruct()
    {
        try {
            while (!$this->buffer->isEmpty()) {
                $line = $this->buffer->pop(0.001);
                if ($line === false) {
                    break;
                }
                fwrite(STDOUT, $line);
            }
            $this->buffer->close();
        } catch (Throwable $exception) {
            $class = static::class;
            $message = $exception->getMessage();
            $exceptionClass = get_class($exception);
            $trace = $exception->getTraceAsString();
            error_log("[{$class}::__destruct] Caught exception ({$exceptionClass}): {$message}\nStack trace:\n{$trace}");
        }
    }
}