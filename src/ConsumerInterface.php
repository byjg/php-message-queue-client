<?php

namespace ByJG\MessageQueueClient;

use Psr\Log\LoggerInterface;

interface ConsumerInterface
{
    public function getLogger(): LoggerInterface;

    public function getLogOutputMessageStart(Message $message): string;

    public function getLogOutputException(\Throwable $exception): string;

    public function getLogOutputSuccess(Message $message): string;

    public function consume(Message $message): void;
}