<?php

namespace SimplyCodedSoftware\IntegrationMessaging\Http\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * Class HttpInboundGatewayAnnotation
 * @package SimplyCodedSoftware\IntegrationMessaging\Http\Annotation
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 * @Annotation
 */
class HttpInboundGatewayAnnotation
{
    /**
     * @var string
     * @Required()
     */
    public $requestChannelName;
    /**
     * @var array<string>
     */
    public $messageConverterNames = [];
    /**
     * @var array<\SimplyCodedSoftware\IntegrationMessaging\Http\Annotation\MappedHeaderAnnotation>
     */
    public $requestHeadersMapper = [];
    /**
     * @var array<\SimplyCodedSoftware\IntegrationMessaging\Http\Annotation\MappedHeaderAnnotation>
     */
    public $responseHeadersMapper = [];
    /**
     * @var string
     */
    public $requestType = "";
    /**
     * @var string
     */
    public $responseType = "";
}