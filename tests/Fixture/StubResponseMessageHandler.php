<?php

namespace Fixture;

use Psr\Http\Message\ResponseInterface;
use SimplyCodedSoftware\IntegrationMessaging\Message;
use SimplyCodedSoftware\IntegrationMessaging\MessageChannel;
use SimplyCodedSoftware\IntegrationMessaging\MessageHandler;
use SimplyCodedSoftware\IntegrationMessaging\Support\MessageBuilder;

/**
 * Class DumbMessageHandler
 * @package Test\SimplyCodedSoftware\IntegrationMessaging\Http
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class StubResponseMessageHandler implements MessageHandler
{
    /**
     * @var Message|null
     */
    private $message;
    /**
     * @var mixed
     */
    private $reply;

    /**
     * StubHttpResponseMessageHandler constructor.
     * @param mixed $reply
     */
    private function __construct($reply)
    {
        $this->reply = $reply;
    }

    public static function create($reply) : self
    {
        return new self($reply);
    }

    public static function createNoReply() : self
    {
        return new self("");
    }

    /**
     * @inheritDoc
     */
    public function handle(Message $message): void
    {
        $this->message = $message;

        /** @var MessageChannel $replyChannel */
        $replyChannel = $message->getHeaders()->getReplyChannel();
        if ($replyChannel && $this->reply) {
            $replyChannel->send(MessageBuilder::withPayload($this->reply)->build());
        }
    }

    public function getMessage() : ?Message
    {
        return $this->message;
    }
}