<?php

namespace Test\SimplyCodedSoftware\IntegrationMessaging\Http;

use Fixture\DumbHttpMessageConverter;
use Fixture\ResponseMother;
use Fixture\ServerRequestMother;
use PHPUnit\Framework\TestCase;
use SimplyCodedSoftware\IntegrationMessaging\Config\ConfigurationException;
use SimplyCodedSoftware\IntegrationMessaging\Http\MediaType;
use SimplyCodedSoftware\IntegrationMessaging\Http\MessageConverter\StringMessageConverter;
use SimplyCodedSoftware\IntegrationMessaging\Http\CompositeConverterFactory;
use SimplyCodedSoftware\IntegrationMessaging\Support\InvalidArgumentException;
use SimplyCodedSoftware\IntegrationMessaging\Support\MessageBuilder;

/**
 * Class HttpMessageConverterFactoryTest
 * @package Test\SimplyCodedSoftware\IntegrationMessaging\Http
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class CompositeConverterFactoryTest extends TestCase
{

    public function test_creating_composite_converter()
    {
        $converterName = "converter-a";
        $dumbHttpMessageConverter = DumbHttpMessageConverter::createWith($converterName, true, false);
        $converter = $this->createMessageConverter([$dumbHttpMessageConverter])->getMessageConvertersWithNames([$converterName]);

        $this->assertFalse($converter->canRead(ServerRequestMother::createGet(), MediaType::createApplicationJson()));
        $this->assertTrue($converter->canWrite(MessageBuilder::withPayload("a")->build(), MediaType::createApplicationJson()));
    }

    public function test_using_string_message_converter_if_no_convert_names_given()
    {
        $converter = $this->createMessageConverter([])->getMessageConvertersWithNames([]);

        $this->assertTrue($converter->canRead(ServerRequestMother::createPostWithBody("some body"), MediaType::createApplicationJson()));
    }

    public function test_throwing_exception_if_no_convert_found_by_name()
    {
        $this->expectException(ConfigurationException::class);

        $this->createMessageConverter([])->getMessageConvertersWithNames(["some-not-existing"]);
    }

    public function test_writing_and_reading_message()
    {
        $converterName = "converter-a";
        $readResult = MessageBuilder::withPayload("some");
        $writeResult = ResponseMother::createSuccess();

        $dumbHttpMessageConverter = DumbHttpMessageConverter::createWith($converterName, true, true)
                                        ->withReadResult($readResult)
                                        ->withWriteResult($writeResult);
        $converter = $this->createMessageConverter([$dumbHttpMessageConverter])->getMessageConvertersWithNames([$converterName]);

        $this->assertEquals(
            $readResult,
            $converter->read(ServerRequestMother::createPost(), MediaType::createApplicationJson())
        );

        $this->assertEquals(
            $writeResult,
            $converter->write(MessageBuilder::withPayload("some")->build(), MediaType::createApplicationJson())
        );
    }

    public function test_throwing_exception_if_trying_to_read_when_not_possible()
    {
        $converter = $this->createMessageConverter([DumbHttpMessageConverter::createNotWritableAndReadable("some-a")])->getMessageConvertersWithNames(["some-a"]);

        $this->expectException(InvalidArgumentException::class);

        $converter->read(ServerRequestMother::createGet(), MediaType::createApplicationJson());
    }

    public function test_throwing_exception_if_trying_to_write_when_not_possible()
    {
        $converter = $this->createMessageConverter([DumbHttpMessageConverter::createNotWritableAndReadable("some-a")])->getMessageConvertersWithNames(["some-a"]);

        $this->expectException(InvalidArgumentException::class);

        $converter->write(MessageBuilder::withPayload(new \stdClass())->build(), MediaType::createApplicationJson());
    }

    /**
     * @param array $httpMessageConverters
     * @return CompositeConverterFactory
     */
    private function createMessageConverter(array $httpMessageConverters): CompositeConverterFactory
    {
        $messageConverterFactory = new CompositeConverterFactory($httpMessageConverters);

        return $messageConverterFactory;
    }
}