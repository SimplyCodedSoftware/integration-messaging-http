<?php

namespace Test\SimplyCodedSoftware\IntegrationMessaging\Http;

use Doctrine\Common\Annotations\AnnotationReader;
use Fixture\Annotation\InboundGateway\HttpInboundGatewayExample;
use Fixture\Configuration\DumbConfigurationObserver;
use Fixture\Configuration\DumbModuleConfigurationRetrievingService;
use PHPUnit\Framework\TestCase;
use SimplyCodedSoftware\IntegrationMessaging\Config\Annotation\AnnotationConfiguration;
use SimplyCodedSoftware\IntegrationMessaging\Config\Annotation\DoctrineClassMetadataReader;
use SimplyCodedSoftware\IntegrationMessaging\Config\Annotation\FileSystemClassLocator;
use SimplyCodedSoftware\IntegrationMessaging\Config\InMemoryConfigurationVariableRetrievingService;
use SimplyCodedSoftware\IntegrationMessaging\Config\MessagingSystemConfiguration;
use SimplyCodedSoftware\IntegrationMessaging\Handler\InMemoryReferenceSearchService;
use SimplyCodedSoftware\IntegrationMessaging\Http\AnnotationHttpConfiguration;
use SimplyCodedSoftware\IntegrationMessaging\Http\CompositeConverterFactory;
use SimplyCodedSoftware\IntegrationMessaging\Http\HttpInboundGateway;
use SimplyCodedSoftware\IntegrationMessaging\Http\HttpInboundGatewayBuilder;

/**
 * Class AnnotationHttpConfigurationTest
 * @package Test\SimplyCodedSoftware\IntegrationMessaging\Http
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class AnnotationHttpConfigurationTest extends TestCase
{
    public function test_creating_with_default_converters()
    {
        $configuration = $this->createMessagingSystemConfiguration();
        $annotationConfiguration = $this->createAnnotationConfiguration("InboundGateway");

        $annotationConfiguration->registerWithin($configuration, InMemoryConfigurationVariableRetrievingService::createEmpty());

        $this->assertEquals(
            $this->createMessagingSystemConfiguration()
                ->registerGatewayBuilder(
                    HttpInboundGatewayBuilder::create(
                        new CompositeConverterFactory([]),
                    "http-inbound", HttpInboundGatewayExample::class, "execute", "request-channel"
                    )
                        ->withMessageConverterList(["string", "object"])
                        ->withRequestHeadersToMessageMapping(["authorization" => "token"])
                        ->withMessageHeadersToResponseMapping(["token" => "authorization"])
                        ->withRequestMediaType("json")
                        ->witResponseMediaType("json")
                ),
            $configuration
        );
    }

    /**
     * @param string $namespacePart
     * @return AnnotationConfiguration
     */
    public function createAnnotationConfiguration(string $namespacePart) : AnnotationConfiguration
    {
        $annotationReader = new AnnotationReader();

        return AnnotationHttpConfiguration::createAnnotationConfiguration(
            [],
            InMemoryConfigurationVariableRetrievingService::createEmpty(),
            new FileSystemClassLocator(
                $annotationReader,
                [
                    __DIR__ . "/../Fixture/Annotation"
                ],
                [
                    "Fixture\Annotation\\" . $namespacePart
                ]
            ),
            new DoctrineClassMetadataReader(
                $annotationReader
            )
        );
    }

    /**
     * @return MessagingSystemConfiguration
     */
    protected function createMessagingSystemConfiguration(): MessagingSystemConfiguration
    {
        return MessagingSystemConfiguration::prepare(
            DumbModuleConfigurationRetrievingService::createEmpty(),
            InMemoryConfigurationVariableRetrievingService::createEmpty(),
            DumbConfigurationObserver::create()
        );
    }
}