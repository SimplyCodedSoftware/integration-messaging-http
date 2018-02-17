<?php

namespace Test\SimplyCodedSoftware\IntegrationMessaging\Http;

use PHPUnit\Framework\TestCase;
use SimplyCodedSoftware\IntegrationMessaging\Http\MediaType;
use SimplyCodedSoftware\IntegrationMessaging\Support\InvalidArgumentException;

/**
 * Class MediaType
 * @package Test\SimplyCodedSoftware\IntegrationMessaging\Http
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class MediaTypeTest extends TestCase
{
    public function test_creating_with_uppper_media_type()
    {
        $type = "Wrong";
        $mediaType = MediaType::createWith($type);

        $this->assertEquals("wrong", $mediaType->getType());
    }

    public function test_creating_media_type()
    {
        $mediaType = MediaType::createWith(MediaType::APPLICATION_JSON_VALUE);

        $this->assertEquals(MediaType::APPLICATION_JSON_VALUE, $mediaType->getType());
        $this->assertTrue($mediaType->hasType(MediaType::APPLICATION_JSON_VALUE));
        $this->assertFalse($mediaType->hasType(MediaType::APPLICATION_ATOM_XML_VALUE));
    }
}