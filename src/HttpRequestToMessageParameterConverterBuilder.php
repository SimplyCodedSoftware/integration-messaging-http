<?php

namespace SimplyCodedSoftware\IntegrationMessaging\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use SimplyCodedSoftware\IntegrationMessaging\Handler\Gateway\MethodArgument;
use SimplyCodedSoftware\IntegrationMessaging\Handler\Gateway\ParameterToMessageConverter;
use SimplyCodedSoftware\IntegrationMessaging\Handler\Gateway\ParameterToMessageConverterBuilder;
use SimplyCodedSoftware\IntegrationMessaging\Support\MessageBuilder;

/**
 * Class HttpRequestToMessageParameterConverter
 * @package SimplyCodedSoftware\IntegrationMessaging\Http
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class HttpRequestToMessageParameterConverterBuilder implements ParameterToMessageConverterBuilder
{
    /**
     * @var HttpMessageConverter
     */
    private $httpMessageConverter;
    /**
     * @var string[]
     */
    private $requestHeadersToMessageMapper;
    /**
     * @var MediaType
     */
    private $requestMediaType;

    /**
     * HttpRequestToMessageParameterConverterBuilder constructor.
     * @param MediaType $requestMediaType
     * @param HttpMessageConverter $httpMessageConverter
     * @param string[] $requestHeadersToMessageMapper
     */
    public function __construct(MediaType $requestMediaType, HttpMessageConverter $httpMessageConverter, array $requestHeadersToMessageMapper)
    {
        $this->requestMediaType = $requestMediaType;
        $this->httpMessageConverter = $httpMessageConverter;
        $this->requestHeadersToMessageMapper = $requestHeadersToMessageMapper;
    }

    /**
     * @inheritDoc
     */
    public function build(): ParameterToMessageConverter
    {
        return new class($this->requestMediaType, $this->httpMessageConverter, $this->requestHeadersToMessageMapper) implements ParameterToMessageConverter
        {
            /**
             * @var HttpMessageConverter
             */
            private $httpMessageConverter;
            /**
             * @var array|\string[]
             */
            private $requestHeadersToMessageMapper;
            /**
             * @var MediaType
             */
            private $requestMediaType;

            /**
             *  constructor.
             * @param MediaType $requestMediaType
             * @param HttpMessageConverter $httpMessageConverter
             * @param string[] $requestHeadersToMessageMapper
             */
            public function __construct(MediaType $requestMediaType, HttpMessageConverter $httpMessageConverter, array $requestHeadersToMessageMapper)
            {
                $this->requestMediaType = $requestMediaType;
                $this->httpMessageConverter = $httpMessageConverter;
                $this->requestHeadersToMessageMapper = $requestHeadersToMessageMapper;
            }

            /**
             * @inheritDoc
             */
            public function convertToMessage(MethodArgument $methodArgument, MessageBuilder $messageBuilder): MessageBuilder
            {
                /** @var ServerRequestInterface $request */
                $request = $methodArgument->value();
                $requestMessageBuilder = $this->httpMessageConverter->read($request, $this->requestMediaType);

                foreach ($this->requestHeadersToMessageMapper as $requestHeader => $messageHeader) {
                    $requestMessageBuilder = $requestMessageBuilder->setHeader($messageHeader, $request->getHeader($requestHeader)[0]);
                }

                return $requestMessageBuilder;
            }

            /**
             * @inheritDoc
             */
            public function isSupporting(MethodArgument $methodArgument): bool
            {
                return is_subclass_of((string)$methodArgument->getParameter()->getType(), RequestInterface::class);
            }
        };
    }
}