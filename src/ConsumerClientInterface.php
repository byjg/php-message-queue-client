<?php

namespace ByJG\MessageQueueClient;

use ByJG\MessageQueueClient\Connector\ConnectorInterface;
use ByJG\MessageQueueClient\Connector\Pipe;
use Psr\Log\LoggerInterface;

interface ConsumerClientInterface
{
    public function getPipe(): Pipe;

    public function getConnector(): ConnectorInterface;

    public function getLogger(): LoggerInterface;

    public function getLogOutputStart(Message $message): string;

    public function getLogOutputException(\Throwable $exception, Message $message): string;

    public function getLogOutputSuccess(Message $message): string;

    public function consume(): void;

    public function processMessage(Message $message): void;
}