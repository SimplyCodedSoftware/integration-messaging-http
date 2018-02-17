<?php

namespace Fixture\Annotation\InboundGateway;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SimplyCodedSoftware\IntegrationMessaging\Annotation\MessageEndpointAnnotation;
use SimplyCodedSoftware\IntegrationMessaging\Http\Annotation\HttpInboundGatewayAnnotation;
use SimplyCodedSoftware\IntegrationMessaging\Http\Annotation\MappedHeaderAnnotation;

/**
 * Interface HttpInboundGatewayExample
 * @package Fixture\Annotation\InboundGateway
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 * @MessageEndpointAnnotation(referenceName="http-inbound")
 */
interface HttpInboundGatewayExample
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @HttpInboundGatewayAnnotation(
     *     requestChannelName="request-channel",
     *     messageConverterNames={"string", "object"},
     *     requestHeadersMapper={
     *          @MappedHeaderAnnotation(fromKey="authorization", toKey="token")
     *     },
     *     responseHeadersMapper={
     *          @MappedHeaderAnnotation(fromKey="token", toKey="authorization")
     *     },
     *     requestType="json",
     *     responseType="json"
     * )
     */
    public function execute(ServerRequestInterface $request) : ResponseInterface;
}