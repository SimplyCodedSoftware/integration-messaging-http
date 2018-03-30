<?php

namespace SimplyCodedSoftware\IntegrationMessaging\Http;

use SimplyCodedSoftware\IntegrationMessaging\Annotation\MessageEndpointAnnotation;
use SimplyCodedSoftware\IntegrationMessaging\Annotation\ModuleAnnotation;
use SimplyCodedSoftware\IntegrationMessaging\Config\Annotation\AnnotationModule;
use SimplyCodedSoftware\IntegrationMessaging\Config\Annotation\AnnotationRegistration;
use SimplyCodedSoftware\IntegrationMessaging\Config\Annotation\AnnotationRegistrationService;
use SimplyCodedSoftware\IntegrationMessaging\Config\Configuration;
use SimplyCodedSoftware\IntegrationMessaging\Config\ConfigurationObserver;
use SimplyCodedSoftware\IntegrationMessaging\Config\ConfigurationVariableRetrievingService;
use SimplyCodedSoftware\IntegrationMessaging\Config\ConfiguredMessagingSystem;
use SimplyCodedSoftware\IntegrationMessaging\Config\ModuleExtension;
use SimplyCodedSoftware\IntegrationMessaging\Handler\ReferenceSearchService;
use SimplyCodedSoftware\IntegrationMessaging\Http\Annotation\HttpInboundGatewayAnnotation;
use SimplyCodedSoftware\IntegrationMessaging\Http\Annotation\MappedHeaderAnnotation;

/**
 * Class AnnotationHttpConfiguration
 * @package SimplyCodedSoftware\IntegrationMessaging\Http
 * @author  Dariusz Gafka <dgafka.mail@gmail.com>
 * @ModuleAnnotation()
 */
class HttpModule implements AnnotationModule
{
    public const MODULE_NAME = "httpModule";

    /**
     * @var array|AnnotationRegistration[]
     */
    private $httpInboundGatewayRegistrations;

    /**
     * AnnotationHttpConfiguration constructor.
     *
     * @param AnnotationRegistration[] $httpInboundGatewayRegistrations
     */
    private function __construct(array $httpInboundGatewayRegistrations)
    {
        $this->httpInboundGatewayRegistrations = $httpInboundGatewayRegistrations;
    }

    public static function create(AnnotationRegistrationService $annotationRegistrationService): AnnotationModule
    {
        return new self($annotationRegistrationService->findRegistrationsFor(MessageEndpointAnnotation::class, HttpInboundGatewayAnnotation::class));
    }

    public function getName(): string
    {
        return self::MODULE_NAME;
    }

    public function getConfigurationVariables(): array
    {
        return [];
    }

    public function getRequiredReferences(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function preConfigure(array $moduleExtensions, ConfigurationObserver $configurationObserver): void
    {
        foreach ($this->httpInboundGatewayRegistrations as $gatewayRegistration) {
            $configurationObserver->notifyGatewayBuilderWasRegistered($gatewayRegistration->getReferenceName(), $gatewayRegistration->getClassWithAnnotation(), $gatewayRegistration->getClassWithAnnotation());
        }
    }

    public function registerWithin(Configuration $configuration, array $moduleExtensions, ConfigurationVariableRetrievingService $configurationVariableRetrievingService, ReferenceSearchService $referenceSearchService): void
    {
        $compositeFactory = new CompositeConverterFactory($moduleExtensions);

        foreach ($this->httpInboundGatewayRegistrations as $gatewayRegistration) {
            /** @var HttpInboundGatewayAnnotation $annotation */
            $annotation = $gatewayRegistration->getAnnotationForMethod();

            $requestMappers = [];
            /** @var MappedHeaderAnnotation $requestHeadersMapper */
            foreach ($annotation->requestHeadersMapper as $requestHeadersMapper) {
                $requestMappers[$requestHeadersMapper->fromKey] = $requestHeadersMapper->toKey;
            }
            $responseMappers = [];
            foreach ($annotation->responseHeadersMapper as $responseHeadersMapper) {
                $responseMappers[$responseHeadersMapper->fromKey] = $responseHeadersMapper->toKey;
            }

            $configuration->registerGatewayBuilder(
                HttpInboundGatewayBuilder::create(
                    $compositeFactory,
                    $gatewayRegistration->getReferenceName(),
                    $gatewayRegistration->getClassWithAnnotation(),
                    $gatewayRegistration->getMethodName(),
                    $annotation->requestChannelName
                )
                    ->withRequestMediaType($annotation->requestType)
                    ->witResponseMediaType($annotation->responseType)
                    ->withMessageConverterList($annotation->messageConverterNames)
                    ->withRequestHeadersToMessageMapping($requestMappers)
                    ->withMessageHeadersToResponseMapping($responseMappers)
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function postConfigure(ConfiguredMessagingSystem $configuredMessagingSystem): void
    {
        return;
    }
}