<?php

namespace Test\SimplyCodedSoftware\IntegrationMessaging\Http\MessageConverter;

use Fixture\ServerRequestMother;
use GuzzleHttp\Psr7\BufferStream;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Stream;
use PHPUnit\Framework\TestCase;
use SimplyCodedSoftware\IntegrationMessaging\Config\InMemoryConfigurationVariableRetrievingService;
use SimplyCodedSoftware\IntegrationMessaging\Http\HttpMessageConverter;
use SimplyCodedSoftware\IntegrationMessaging\Http\MediaType;
use SimplyCodedSoftware\IntegrationMessaging\Http\MessageConverter\StringMessageConverter;
use SimplyCodedSoftware\IntegrationMessaging\Support\MessageBuilder;
use SimplyCodedSoftware\IntegrationMessaging\Support\MessageCompareService;
use Test\SimplyCodedSoftware\IntegrationMessaging\Http\ResponseCompareService;

/**
 * Class BodyToPayloadMessageConverter
 * @package Test\SimplyCodedSoftware\IntegrationMessaging\Http\MessageConverter
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class StringMessageConverterTest extends TestCase
{
    public function test_get_converter_name()
    {
        $converter = $this->createConverter();

        $this->assertEquals(StringMessageConverter::CONVERTER_NAME, $converter->getConverterName());
    }

    public function test_not_be_able_to_convert_messages_different_than_post_and_put()
    {
        $converter = $this->createConverter();

        $this->assertTrue($converter->canRead(ServerRequestMother::createPost(), MediaType::createApplicationJson()));
        $this->assertTrue($converter->canRead(ServerRequestMother::createPUT(), MediaType::createApplicationJson()));
    }

    public function test_converting_body_to_payload_message()
    {
        $body = '{"body": {"some": "value"}}';
        $bufferedStream = $this->createStream($body);
        $message = $this->createConverter()->read(ServerRequestMother::createPost()->withBody($bufferedStream), MediaType::createApplicationJson());

        $this->assertEquals(
            MessageBuilder::withPayload($body),
            $message
        );
    }

    public function test_converting_from_message_to_response()
    {
        $body = '{"body": {"some": "value"}}';
        $response = new Response(200, [
            HttpMessageConverter::CONTENT_TYPE => MediaType::APPLICATION_JSON_VALUE
        ], $body);

        $this->assertTrue(ResponseCompareService::compare($response, $this->createConverter()->write(MessageBuilder::withPayload($body)->build(), MediaType::createApplicationJson())));
    }

    public function test_not_writing_if_payload_is_not_string()
    {
        $this->assertFalse($this->createConverter()->canWrite(MessageBuilder::withPayload(new \stdClass())->build(), MediaType::createApplicationJson()));
    }

    public function test_writing_with_success_if_payload_is_string()
    {
        $this->assertTrue($this->createConverter()->canWrite(MessageBuilder::withPayload("some")->build(), MediaType::createApplicationJson()));
    }

    public function test_using_media_type_as_message_payload_if_no_request_body_found()
    {
        $message = $this->createConverter()->read(ServerRequestMother::createGet(), MediaType::createApplicationJson());

        $this->assertEquals(
            MessageBuilder::withPayload(MediaType::APPLICATION_JSON_VALUE),
            $message
        );
    }

    /**
     * @return StringMessageConverter
     */
    private function createConverter(): StringMessageConverter
    {
        /** @var StringMessageConverter $converter */
        $converter = StringMessageConverter::create(InMemoryConfigurationVariableRetrievingService::createEmpty());
        return $converter;
    }

    /**
     * @param string $body
     * @return BufferStream
     */
    private function createStream(string $body): BufferStream
    {
        $bufferedStream = new BufferStream();
        $bufferedStream->write($body);
        return $bufferedStream;
    }
}