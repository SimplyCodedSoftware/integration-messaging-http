<?php

namespace Test\SimplyCodedSoftware\IntegrationMessaging\Http;

use Fixture\DumbHttpMessageConverter;
use Fixture\ResponseMother;
use Fixture\ServerRequestMother;
use Fixture\StubResponseMessageHandler;
use PHPUnit\Framework\TestCase;
use SimplyCodedSoftware\IntegrationMessaging\Channel\DirectChannel;
use SimplyCodedSoftware\IntegrationMessaging\Config\InMemoryChannelResolver;
use SimplyCodedSoftware\IntegrationMessaging\Handler\InMemoryReferenceSearchService;
use SimplyCodedSoftware\IntegrationMessaging\Http\CompositeConverterFactory;
use SimplyCodedSoftware\IntegrationMessaging\Http\HttpInboundGateway;
use SimplyCodedSoftware\IntegrationMessaging\Http\HttpInboundGatewayBuilder;
use SimplyCodedSoftware\IntegrationMessaging\Support\MessageBuilder;
use SimplyCodedSoftware\IntegrationMessaging\Support\MessageCompareService;

/**
 * Class HttpInboundGatewayBuilderTest
 * @package Test\SimplyCodedSoftware\IntegrationMessaging\Http
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class HttpInboundGatewayBuilderTest extends TestCase
{
    public function test_converting_and_sending_message()
    {
        $requestChannelName = "request-channel";
        $requestChannel = DirectChannel::create();
        $replyData = "someReply";
        $requestChannel->subscribe(StubResponseMessageHandler::create($replyData));

        /** @var HttpInboundGateway $gateway */
        $gateway = HttpInboundGatewayBuilder::create(new CompositeConverterFactory([]), "some", HttpInboundGateway::class, "execute", $requestChannelName)
            ->build(InMemoryReferenceSearchService::createEmpty(), $this->createChannelResolver($requestChannelName, $requestChannel));

        $this->assertEquals(
            $replyData,
            (string)$gateway->execute(ServerRequestMother::createGet())->getBody()
        );
    }

    public function test_creating_response_with_message_converter()
    {
        $requestChannelName = "request-channel";
        $requestChannel = DirectChannel::create();
        $messageHandler = StubResponseMessageHandler::create("reply");
        $requestChannel->subscribe($messageHandler);
        $requestMessage = MessageBuilder::withPayload("some");
        $response = ResponseMother::createSuccessWithBody("someSuperResult");

        /** @var HttpInboundGateway $gateway */
        $convertName = "dumbConverter";
        $gateway = HttpInboundGatewayBuilder::create(
            new CompositeConverterFactory([DumbHttpMessageConverter::createWritableAndReadable($convertName)
                ->withReadResult($requestMessage)
                ->withWriteResult($response)
            ]),
            "some",HttpInboundGateway::class, "execute",
            $requestChannelName
        )
            ->withMessageConverterList([$convertName])
            ->build(InMemoryReferenceSearchService::createEmpty(), $this->createChannelResolver($requestChannelName, $requestChannel));

        $gatewayResponse = $gateway->execute(ServerRequestMother::createPostWithBody("some"));

        $this->assertTrue(
            MessageCompareService::areSameMessagesIgnoringIdAndTimestamp($requestMessage->build(), $messageHandler->getMessage())
        );

        $this->assertEquals(
            $response,
            $gatewayResponse
        );
    }

    public function test_creating_response_with_header_mappers()
    {
        $requestChannelName = "request-channel";
        $requestChannel = DirectChannel::create();
        $messageHandler = StubResponseMessageHandler::create("reply");
        $requestChannel->subscribe($messageHandler);
        $requestMessage = MessageBuilder::withPayload("some");
        $response = ResponseMother::createSuccessWithBody("someSuperResult");

        /** @var HttpInboundGateway $gateway */
        $convertName = "dumbConverter";
        $gateway = HttpInboundGatewayBuilder::create(
            new CompositeConverterFactory([DumbHttpMessageConverter::createWritableAndReadable($convertName)
                ->withReadResult($requestMessage)
                ->withWriteResult($response)
            ]),
            "some",HttpInboundGateway::class, "execute",
            $requestChannelName
        )
            ->withMessageConverterList([$convertName])
            ->withRequestHeadersToMessageMapping([
                "Authorization" => "token"
            ])
            ->withMessageHeadersToResponseMapping([
                "timestamp" => "when"
            ])
            ->build(InMemoryReferenceSearchService::createEmpty(), $this->createChannelResolver($requestChannelName, $requestChannel));

        $gatewayResponse = $gateway->execute(
            ServerRequestMother::createPostWithBody("some")
                ->withHeader("Authorization", "123")
        );

        $this->assertTrue($messageHandler->getMessage()->getHeaders()->containsKey("token"));
        $this->assertEquals($messageHandler->getMessage()->getHeaders()->get("token"), "123");
        $this->assertArrayHasKey("when", $gatewayResponse->getHeaders());
    }

    public function test_returning_http_response_when_no_response_message_was_deliver()
    {
        $requestChannelName = "request-channel";
        $requestChannel = DirectChannel::create();
        $messageHandler = StubResponseMessageHandler::createNoReply();
        $requestChannel->subscribe($messageHandler);

        /** @var HttpInboundGateway $gateway */
        $gateway = HttpInboundGatewayBuilder::create(
            new CompositeConverterFactory([]),
            "some",HttpInboundGateway::class, "execute",
            $requestChannelName
        )
            ->build(InMemoryReferenceSearchService::createEmpty(), $this->createChannelResolver($requestChannelName, $requestChannel));

        $gatewayResponse = $gateway->execute(
            ServerRequestMother::createPostWithBody("some")
                ->withHeader("Authorization", "123")
        );

        $this->assertEquals(200, $gatewayResponse->getStatusCode());
        $this->assertEquals("", (string)$gatewayResponse->getBody());
    }

    /**
     * @param $requestChannelName
     * @param $requestChannel
     * @return InMemoryChannelResolver
     */
    private function createChannelResolver($requestChannelName, $requestChannel): InMemoryChannelResolver
    {
        return InMemoryChannelResolver::createFromAssociativeArray([
            $requestChannelName => $requestChannel
        ]);
    }
}