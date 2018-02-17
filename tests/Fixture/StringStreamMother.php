<?php

namespace Fixture;

use GuzzleHttp\Psr7\BufferStream;
use Psr\Http\Message\StreamInterface;

/**
 * Class StringStream
 * @package Fixture
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class StringStreamMother
{
    /**
     * @param string $body
     * @return StreamInterface
     */
    public static function create(string $body) : StreamInterface
    {
        $bufferedStream = new BufferStream();
        $bufferedStream->write($body);

        return $bufferedStream;
    }
}