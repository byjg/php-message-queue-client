<?php

namespace ByJG\MessageQueueClient;

use ByJG\MessageQueueClient\Connector\ConnectorInterface;
use ByJG\MessageQueueClient\Connector\Pipe;
use ByJG\MessageQueueClient\Envelope as MQEnvelope;
use Psr\Log\LoggerInterface;
use Throwable;

trait ConsumerClientTrait
{
    abstract public function getPipe(): Pipe;

    abstract public function getConnector(): ConnectorInterface;

    abstract public function getLogger(): LoggerInterface;

    abstract public function getLogOutputStart(Message $message): string;

    abstract public function getLogOutputException(Throwable $exception, Message $message): string;

    abstract public function getLogOutputSuccess(Message $message): string;

    public function consume(): void
    {
        $connector = $this->getConnector();

        $pipe = $this->getPipe();

        $connector->consume($pipe, function (MQEnvelope $envelope) {
            $this->getLogger()->info($this->getLogOutputStart($envelope->getMessage()));
            $this->processMessage($envelope->getMessage());
            $this->getLogger()->info($this->getLogOutputSuccess($envelope->getMessage()));
            return Message::ACK;
        }, function (MQEnvelope $envelope, $ex) {
            $this->getLogger()->info($this->getLogOutputException($ex, $envelope->getMessage()));
            return Message::NACK;
        });
    }

    abstract public function processMessage(Message $message): void;
}