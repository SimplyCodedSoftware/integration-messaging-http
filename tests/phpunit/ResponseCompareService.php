<?php

namespace Test\SimplyCodedSoftware\IntegrationMessaging\Http;

use Psr\Http\Message\ResponseInterface;

/**
 * Class ResponseCompareService
 * @package Test\SimplyCodedSoftware\IntegrationMessaging\Http
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class ResponseCompareService
{
    public static function compare(ResponseInterface $response, ResponseInterface $toCompare) : bool
    {
        return (string)$response->getBody() === (string)$toCompare->getBody() && $response->getHeaders() == $toCompare->getHeaders() && $response->getStatusCode() === $toCompare->getStatusCode();
    }
}