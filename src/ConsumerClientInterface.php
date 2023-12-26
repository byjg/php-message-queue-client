<?php

namespace ByJG\MessageQueueClient;

use ByJG\MessageQueueClient\Connector\ConnectorInterface;
use Psr\Log\LoggerInterface;

interface ConsumerClientInterface
{
    public function getPipe(): string;

    public function getConnector(): ConnectorInterface;

    public function getLogger(): LoggerInterface;

    public function getLogOutputMessageStart(Message $message): string;

    public function getLogOutputException(\Throwable $exception): string;

    public function getLogOutputSuccess(Message $message): string;

    public function consume(): void;

    public function processMessage(Message $message): void;
}