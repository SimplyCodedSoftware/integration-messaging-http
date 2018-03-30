<?php

namespace SimplyCodedSoftware\IntegrationMessaging\Http;

use GuzzleHttp\Psr7\Response;
use SimplyCodedSoftware\IntegrationMessaging\Channel\DirectChannel;
use SimplyCodedSoftware\IntegrationMessaging\Channel\QueueChannel;
use SimplyCodedSoftware\IntegrationMessaging\Handler\Gateway\CustomSendAndReceiveService;
use SimplyCodedSoftware\IntegrationMessaging\Handler\Gateway\SendAndReceiveService;
use SimplyCodedSoftware\IntegrationMessaging\Handler\InterfaceToCall;
use SimplyCodedSoftware\IntegrationMessaging\Message;
use SimplyCodedSoftware\IntegrationMessaging\MessageChannel;
use SimplyCodedSoftware\IntegrationMessaging\PollableChannel;
use SimplyCodedSoftware\IntegrationMessaging\Support\MessageBuilder;

/**
 * Class HttpSendAndReceiveService
 * @package SimplyCodedSoftware\IntegrationMessaging\Http
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class HttpSendAndReceiveService implements CustomSendAndReceiveService
{
    /**
     * @var DirectChannel
     */
    private $requestChannel;
    /**
     * @var PollableChannel
     */
    private $replyChannel;
    /**
     * @var MessageChannel
     */
    private $errorChannel;
    /**
     * @var HttpMessageConverter
     */
    private $httpMessageConverter;
    /**
     * @var string[]
     */
    private $messageHeadersToResponseMapper;
    /**
     * @var MediaType
     */
    private $responseMediaType;

    /**
     * DefaultReplySender constructor.
     * @param QueueChannel $replyChannel
     * @param MediaType $responseMediaType
     * @param HttpMessageConverter $httpMessageConverter
     * @param string[] $messageHeadersToResponseMapper
     * @internal param DirectChannel $requestChannel
     */
    private function __construct(QueueChannel $replyChannel, MediaType $responseMediaType, HttpMessageConverter $httpMessageConverter, array $messageHeadersToResponseMapper)
    {
        $this->replyChannel = $replyChannel;
        $this->httpMessageConverter = $httpMessageConverter;
        $this->responseMediaType = $responseMediaType;
        $this->messageHeadersToResponseMapper = $messageHeadersToResponseMapper;
    }

    /**
     * @param MediaType $responseMediaType
     * @param HttpMessageConverter $httpMessageConverter
     * @param string[] $messageHeadersToResponseMapper
     * @return HttpSendAndReceiveService
     */
    public static function create(MediaType $responseMediaType, HttpMessageConverter $httpMessageConverter, array $messageHeadersToResponseMapper) : self
    {
        return new self(QueueChannel::create(), $responseMediaType, $httpMessageConverter, $messageHeadersToResponseMapper);
    }

    /**
     * @inheritDoc
     */
    public function setSendAndReceive(DirectChannel $requestChannel, ?PollableChannel $replyChannel, ?MessageChannel $errorChannel): void
    {
        $this->requestChannel = $requestChannel;
        $this->replyChannel = $replyChannel ? $replyChannel : $this->replyChannel;
        $this->errorChannel = $errorChannel;
    }

    /**
     * @inheritDoc
     */
    public function prepareForSend(MessageBuilder $messageBuilder, InterfaceToCall $interfaceToCall): MessageBuilder
    {
        if (!$interfaceToCall->hasReturnValue()) {
            return $messageBuilder;
        }

        return $messageBuilder
            ->setErrorChannel($this->errorChannel ? $this->errorChannel : $this->replyChannel)
            ->setReplyChannel($this->replyChannel);
    }

    /**
     * @inheritDoc
     */
    public function send(Message $message): void
    {
        $this->requestChannel->send($message);
    }

    /**
     * @inheritDoc
     */
    public function receiveReply(): ?Message
    {
        $receivedMessage = $this->replyChannel->receive();
        if (!$receivedMessage) {
            return MessageBuilder::withPayload(new Response())->build();
        }

        $responsePayload = $this->httpMessageConverter->write($receivedMessage, $this->responseMediaType);

        foreach ($this->messageHeadersToResponseMapper as $messageHeader => $responseHeader) {
            $responsePayload = $responsePayload->withHeader($responseHeader, $receivedMessage->getHeaders()->get($messageHeader));
        }

        return MessageBuilder::withPayload($responsePayload)->build();
    }
}