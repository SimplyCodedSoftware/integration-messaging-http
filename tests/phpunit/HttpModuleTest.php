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
use SimplyCodedSoftware\IntegrationMessaging\Config\Annotation\InMemoryAnnotationRegistrationService;
use SimplyCodedSoftware\IntegrationMessaging\Config\InMemoryConfigurationVariableRetrievingService;
use SimplyCodedSoftware\IntegrationMessaging\Config\InMemoryModuleMessaging;
use SimplyCodedSoftware\IntegrationMessaging\Config\MessagingSystemConfiguration;
use SimplyCodedSoftware\IntegrationMessaging\Config\NullObserver;
use SimplyCodedSoftware\IntegrationMessaging\Handler\InMemoryReferenceSearchService;
use SimplyCodedSoftware\IntegrationMessaging\Http\HttpModule;
use SimplyCodedSoftware\IntegrationMessaging\Http\CompositeConverterFactory;
use SimplyCodedSoftware\IntegrationMessaging\Http\HttpInboundGateway;
use SimplyCodedSoftware\IntegrationMessaging\Http\HttpInboundGatewayBuilder;

/**
 * Class AnnotationHttpConfigurationTest
 * @package Test\SimplyCodedSoftware\IntegrationMessaging\Http
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class HttpModuleTest extends TestCase
{
    public function test_creating_with_default_converters()
    {
        $annotationConfiguration = $this->prepareConfiguration([HttpInboundGatewayExample::class]);

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
            $annotationConfiguration
        );
    }

    /**
     * @param array $annotationClassesToRegister
     *
     * @return MessagingSystemConfiguration
     */
    private function prepareConfiguration(array $annotationClassesToRegister): MessagingSystemConfiguration
    {
        $annotationRegistrationService = InMemoryAnnotationRegistrationService::createFrom($annotationClassesToRegister);
        $cqrsMessagingModule           = HttpModule::create($annotationRegistrationService);

        $extendedConfiguration = $this->createMessagingSystemConfiguration();
        $cqrsMessagingModule->prepare(
            $extendedConfiguration,
            [],
            NullObserver::create()
        );

        return $extendedConfiguration;
    }

    /**
     * @return MessagingSystemConfiguration
     */
    protected function createMessagingSystemConfiguration(): MessagingSystemConfiguration
    {
        return MessagingSystemConfiguration::prepare(InMemoryModuleMessaging::createEmpty());
    }
}