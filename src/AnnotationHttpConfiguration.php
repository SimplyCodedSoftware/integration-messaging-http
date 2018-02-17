<?php

namespace SimplyCodedSoftware\IntegrationMessaging\Http;

use SimplyCodedSoftware\IntegrationMessaging\Annotation\MessageEndpointAnnotation;
use SimplyCodedSoftware\IntegrationMessaging\Annotation\ModuleConfigurationAnnotation;
use SimplyCodedSoftware\IntegrationMessaging\Config\Annotation\AnnotationConfiguration;
use SimplyCodedSoftware\IntegrationMessaging\Config\Annotation\ClassLocator;
use SimplyCodedSoftware\IntegrationMessaging\Config\Annotation\ClassMetadataReader;
use SimplyCodedSoftware\IntegrationMessaging\Config\Annotation\ModuleConfiguration\AnnotationClassesWithMethodFinder;
use SimplyCodedSoftware\IntegrationMessaging\Config\Configuration;
use SimplyCodedSoftware\IntegrationMessaging\Config\ConfigurationVariableRetrievingService;
use SimplyCodedSoftware\IntegrationMessaging\Config\ConfiguredMessagingSystem;
use SimplyCodedSoftware\IntegrationMessaging\Http\Annotation\FromMessageHeadersMapperAnnotation;
use SimplyCodedSoftware\IntegrationMessaging\Http\Annotation\HttpInboundGatewayAnnotation;
use SimplyCodedSoftware\IntegrationMessaging\Http\Annotation\MappedHeaderAnnotation;
use SimplyCodedSoftware\IntegrationMessaging\Http\Annotation\ToMessageHeadersMapperAnnotation;

/**
 * Class AnnotationHttpConfiguration
 * @package SimplyCodedSoftware\IntegrationMessaging\Http
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 * @ModuleConfigurationAnnotation(moduleName="httpConfiguration")
 */
class AnnotationHttpConfiguration implements AnnotationConfiguration
{
    /**
     * @var ClassLocator
     */
    private $classLocator;
    /**
     * @var ClassMetadataReader
     */
    private $classMetadataReader;
    /**
     * @var array
     */
    private $moduleConfigurationExtensions;

    /**
     * AnnotationGatewayConfiguration constructor.
     * @param array $moduleConfigurationExtensions
     * @param ClassLocator $classLocator
     * @param ClassMetadataReader $classMetadataReader
     */
    private function __construct(array $moduleConfigurationExtensions, ClassLocator $classLocator, ClassMetadataReader $classMetadataReader)
    {
        $this->moduleConfigurationExtensions = $moduleConfigurationExtensions;
        $this->classLocator = $classLocator;
        $this->classMetadataReader = $classMetadataReader;
    }

    /**
     * @inheritDoc
     */
    public static function createAnnotationConfiguration(array $moduleConfigurationExtensions, ConfigurationVariableRetrievingService $configurationVariableRetrievingService, ClassLocator $classLocator, ClassMetadataReader $classMetadataReader): AnnotationConfiguration
    {
        return new self($moduleConfigurationExtensions, $classLocator, $classMetadataReader);
    }

    /**
     * @inheritDoc
     */
    public function registerWithin(Configuration $configuration, ConfigurationVariableRetrievingService $configurationVariableRetrievingService): void
    {
        $annotationMessageEndpointConfigurationFinder = new AnnotationClassesWithMethodFinder($this->classLocator, $this->classMetadataReader);

        $compositeFactory = new CompositeConverterFactory([]);
        foreach ($annotationMessageEndpointConfigurationFinder->findFor(MessageEndpointAnnotation::class, HttpInboundGatewayAnnotation::class) as $mapperRegistration) {
            /** @var HttpInboundGatewayAnnotation $annotation */
            $annotation = $mapperRegistration->getAnnotation();

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
                    $mapperRegistration->getReferenceName(),
                    $mapperRegistration->getMessageEndpointClass(),
                    $mapperRegistration->getMethodName(),
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
        // TODO: Implement postConfigure() method.
    }
}