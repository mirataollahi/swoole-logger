<?php

namespace Craftix\Logger;

use Swoole\Coroutine;
use Swoole\Coroutine\Channel;

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

    /** Buffer current logs items count */
    private int $bufferItemsCount = 0;

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
                    Logger::handleError('Console logger buffer channel closed unexpectedly');
                    break;
                }
                else if ($logStream instanceof BufferedLog) {
                    $this->bufferItemsCount--;
                    $this->onBufferLogReceive($logStream);
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
        $this->bufferItemsCount++;
        return $this->buffer->push($bufferedLog, $this->pushTimeout);
    }

    /** Close log buffer manager service */
    public function close(): void
    {
        if ($this->isClosing)
            return;

        $this->isClosing = true;
        while (!$this->buffer->isEmpty() && $this->bufferItemsCount > 0) {
            Logger::handleError("Logger buffer closed with buffered logs count $this->bufferItemsCount");
        }
        $this->buffer->close();
    }

    /** Destruct console logs buffer manager instance and its resources */
    public function __destruct()
    {
        $this->close();
    }
}