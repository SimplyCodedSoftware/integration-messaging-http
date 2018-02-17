<?php

namespace Fixture;

use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\ServerRequestInterface;
use SimplyCodedSoftware\IntegrationMessaging\Http\HttpMessageConverter;

/**
 * Class ServerRequest
 * @package Fixture
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class ServerRequestMother
{
    public static function createGet() : ServerRequestInterface
    {
        return self::createServerRequest()
                    ->withMethod(HttpMessageConverter::METHOD_TYPE_GET);
    }

    public static function createPost() : ServerRequestInterface
    {
        return self::createServerRequest()
            ->withMethod(HttpMessageConverter::METHOD_TYPE_POST);
    }

    /**
     * @param string $body
     * @return ServerRequestInterface
     */
    public static function createPostWithBody(string $body) : ServerRequestInterface
    {
        return self::createPost()
                ->withBody(StringStreamMother::create($body));
    }

    public static function createPut() : ServerRequestInterface
    {
        return self::createServerRequest()
            ->withMethod(HttpMessageConverter::METHOD_TYPE_PUT);
    }

    public static function createOptions() : ServerRequestInterface
    {
        return self::createServerRequest()
            ->withMethod(HttpMessageConverter::METHOD_TYPE_OPTIONS);
    }

    /**
     * @return ServerRequestInterface
     */
    private static function createServerRequest(): ServerRequestInterface
    {
        return ServerRequest::fromGlobals();
    }
}