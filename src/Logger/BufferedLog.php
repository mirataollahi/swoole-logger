<?php

namespace App\Services\Logger;

/**
 * Immutable value‑object representing a single log entry queued in the channel.
 * Holds all data needed by the transport (timestamp, level, colour, tags, …).
 */
class BufferedLog
{

    /** Log message text */
    public ?string $message = null;

    /** Log severity level */
    public LogLevel $logLevel = LogLevel::INFO;

    /** Optional contextual tags */
    public array $tags = [];

    /** Service name that produced this log */
    public ?string $serviceName = null;

    /** Epoch‑second timestamp when the log was created */
    public int $createdAt;

    /** Create an instance of buffered log item info before push to logs buffer queue */
    public static function create(): static
    {
        $bufferedLog = new static();
        $bufferedLog->setCreatedAt(microtime(true));
        return $bufferedLog;
    }

    /** Get log pure text message */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /** Set log item message text */
    public function setMessage(?string $message = null): static
    {
        $this->message = $message;
        return $this;
    }

    /** Get buffered log level */
    public function getLogLevel(): LogLevel
    {
        return $this->logLevel;
    }

    /** Set log item level */
    public function setLogLevel(LogLevel $logLevel): static
    {
        $this->logLevel = $logLevel;
        return $this;
    }

    /** Get buffered log tags array */
    public function getTags(): array
    {
        return $this->tags;
    }

    /** Set log item tags list array */
    public function setTags(array $tags): static
    {
        $this->tags = $tags;
        return $this;
    }

    /** Get buffered logs item service name  */
    public function getServiceName(): ?string
    {
        return $this->serviceName;
    }

    /** Set log item service name */
    public function setServiceName(?string $serviceName): static
    {
        $this->serviceName = $serviceName;
        return $this;
    }

    /** Get buffered log created at micro timestamp */
    public function getCreatedAt(): int
    {
        return $this->createdAt;
    }

    /** Set log item fire or created at this micro timestamp */
    public function setCreatedAt(int $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}