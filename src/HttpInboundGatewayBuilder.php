<?php

namespace SimplyCodedSoftware\IntegrationMessaging\Http;

use SimplyCodedSoftware\IntegrationMessaging\Handler\ChannelResolver;
use SimplyCodedSoftware\IntegrationMessaging\Handler\Gateway\GatewayBuilder;
use SimplyCodedSoftware\IntegrationMessaging\Handler\Gateway\GatewayProxyBuilder;
use SimplyCodedSoftware\IntegrationMessaging\Handler\ReferenceSearchService;

/**
 * Class HttpInboundGateway
 * @package SimplyCodedSoftware\IntegrationMessaging\Http
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class HttpInboundGatewayBuilder implements GatewayBuilder
{
    /**
     * @var string
     */
    private $referenceName;
    /**
     * @var string[]
     */
    private $converterNameList = [];
    /**
     * @var string
     */
    private $requestChannelName;
    /**
     * @var string[]
     */
    private $requestHeadersToMessageMapper = [];
    /**
     * @var string[]
     */
    private $messageHeadersToResponseMapper = [];
    /**
     * @var CompositeConverterFactory
     */
    private $compositeConverterFactory;
    /**
     * @var string
     */
    private $requestMediaType = MediaType::APPLICATION_JSON_VALUE;
    /**
     * @var string
     */
    private $responseMediaType = MediaType::APPLICATION_JSON_VALUE;
    /**
     * @var string
     */
    private $methodName;
    /**
     * @var string
     */
    private $className;

    /**
     * HttpInboundGatewayBuilder constructor.
     * @param CompositeConverterFactory $compositeConverterFactory
     * @param string $referenceName
     * @param string $className
     * @param string $methodName
     * @param string $requestChannelName
     */
    private function __construct(CompositeConverterFactory $compositeConverterFactory, string $referenceName, string $className, string $methodName, string $requestChannelName)
    {
        $this->compositeConverterFactory = $compositeConverterFactory;
        $this->referenceName = $referenceName;
        $this->methodName = $methodName;
        $this->className = $className;
        $this->requestChannelName = $requestChannelName;
    }

    /**
     * @param CompositeConverterFactory $compositeConverterFactory
     * @param string $referenceName
     * @param string $className
     * @param string $methodName
     * @param string $requestChannelName
     * @return HttpInboundGatewayBuilder
     */
    public static function create(CompositeConverterFactory $compositeConverterFactory, string $referenceName, string $className, string $methodName, string $requestChannelName) : self
    {
        return new self($compositeConverterFactory, $referenceName, $className, $methodName, $requestChannelName);
    }

    /**
     * @param string[] $convertNameList
     * @return HttpInboundGatewayBuilder
     */
    public function withMessageConverterList(array $convertNameList) : self
    {
        $this->converterNameList = $convertNameList;

        return $this;
    }

    /**
     * @param string[] $mapping
     * @return HttpInboundGatewayBuilder
     */
    public function withRequestHeadersToMessageMapping(array $mapping) : self
    {
        $this->requestHeadersToMessageMapper = $mapping;

        return $this;
    }

    /**
     * @param string[] $mapping
     * @return HttpInboundGatewayBuilder
     */
    public function withMessageHeadersToResponseMapping(array $mapping) : self
    {
        $this->messageHeadersToResponseMapper = $mapping;

        return $this;
    }

    /**
     * @param string $mediaType
     * @return HttpInboundGatewayBuilder
     */
    public function withRequestMediaType(string $mediaType) : self
    {
        $this->requestMediaType = $mediaType;

        return $this;
    }

    /**
     * @param string $mediaType
     * @return HttpInboundGatewayBuilder
     */
    public function witResponseMediaType(string $mediaType) : self
    {
        $this->responseMediaType = $mediaType;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getReferenceName(): string
    {
        return $this->referenceName;
    }

    /**
     * @inheritDoc
     */
    public function getRequestChannelName(): string
    {
        return $this->requestChannelName;
    }

    /**
     * @inheritDoc
     */
    public function getRequiredReferences(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getInterfaceName(): string
    {
        return $this->className;
    }

    /**
     * @inheritDoc
     */
    public function build(ReferenceSearchService $referenceSearchService, ChannelResolver $channelResolver)
    {
        $httpMessageConverter = $this->compositeConverterFactory->getMessageConvertersWithNames($this->converterNameList);

        return GatewayProxyBuilder::create(
            $this->referenceName,
            $this->className,
            $this->methodName,
            $this->requestChannelName

        )   ->withMillisecondTimeout(1)
            ->withCustomSendAndReceiveService(HttpSendAndReceiveService::create(MediaType::createWith($this->responseMediaType), $httpMessageConverter, $this->messageHeadersToResponseMapper))
            ->withParameterToMessageConverters([
                new HttpRequestToMessageParameterConverterBuilder(
                    MediaType::createWith($this->requestMediaType),
                    $httpMessageConverter,
                    $this->requestHeadersToMessageMapper
                )
            ])
            ->build($referenceSearchService, $channelResolver);
    }

    public function __toString()
    {
        return "http-inbound-gateway";
    }
}