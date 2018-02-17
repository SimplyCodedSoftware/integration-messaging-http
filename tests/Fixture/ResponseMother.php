<?php

namespace Fixture;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ResponseMother
 * @package Fixture
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class ResponseMother
{
    /**
     * @return ResponseInterface
     */
    public static function createSuccess() : ResponseInterface
    {
        return new Response(200, [], "");
    }

    /**
     * @param string $body
     * @return ResponseInterface
     */
    public static function createSuccessWithBody(string $body) : ResponseInterface
    {
        return new Response(200, [], $body);
    }

    /**
     * @param array $headers
     * @param string $body
     * @return ResponseInterface
     */
    public static function createSuccessWithHeadersAndBody(array $headers, string $body) : ResponseInterface
    {
        return new Response(200, $headers, $body);
    }
}