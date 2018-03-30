<?php

namespace SimplyCodedSoftware\IntegrationMessaging\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SimplyCodedSoftware\IntegrationMessaging\Config\Annotation\AnnotationModuleExtension;
use SimplyCodedSoftware\IntegrationMessaging\Config\Annotation\AnnotationRegistrationService;
use SimplyCodedSoftware\IntegrationMessaging\Config\ConfigurationVariable;
use SimplyCodedSoftware\IntegrationMessaging\Config\ConfigurationVariableRetrievingService;
use SimplyCodedSoftware\IntegrationMessaging\Config\ModuleConfigurationExtension;
use SimplyCodedSoftware\IntegrationMessaging\Config\RequiredReference;
use SimplyCodedSoftware\IntegrationMessaging\Message;
use SimplyCodedSoftware\IntegrationMessaging\Support\Assert;
use SimplyCodedSoftware\IntegrationMessaging\Support\InvalidArgumentException;
use SimplyCodedSoftware\IntegrationMessaging\Support\MessageBuilder;

/**
 * Class CompositeHttpMessageConverter
 * @package SimplyCodedSoftware\IntegrationMessaging\Http
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 * @internal
 */
class CompositeHttpMessageConverter implements HttpMessageConverter
{
    /**
     * @var HttpMessageConverter[]
     */
    private $httpMessageConverters;

    /**
     * CompositeHttpMessageConverter constructor.
     * @param HttpMessageConverter[] $httpMessageConverters
     */
    private function __construct(array $httpMessageConverters)
    {
        Assert::allInstanceOfType($httpMessageConverters, HttpMessageConverter::class);

        $this->httpMessageConverters = $httpMessageConverters;
    }

    /**
     * @inheritDoc
     */
    public static function create(AnnotationRegistrationService $annotationRegistrationService): AnnotationModuleExtension
    {
        return self::createWithConverters([]);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return HttpModule::MODULE_NAME;
    }

    /**
     * @inheritDoc
     */
    public function getConfigurationVariables(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getRequiredReferences(): array
    {
        return [];
    }

    /**
     * @param HttpMessageConverter[] $converters
     * @return CompositeHttpMessageConverter
     */
    public static function createWithConverters(array $converters) : self
    {
        return new self($converters);
    }

    /**
     * @inheritDoc
     */
    public function getConverterName(): string
    {
        return "composite-http-message-converter";
    }

    /**
     * @inheritDoc
     */
    public function canWrite(Message $message, MediaType $mediaType): bool
    {
        foreach ($this->httpMessageConverters as $converter) {
            if ($converter->canWrite($message, $mediaType)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function canRead(ServerRequestInterface $request, MediaType $mediaType): bool
    {
        foreach ($this->httpMessageConverters as $converter) {
            if ($converter->canRead($request, $mediaType)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function write(Message $message, MediaType $mediaType): ResponseInterface
    {
        foreach ($this->httpMessageConverters as $httpMessageConverter) {
            if ($httpMessageConverter->canWrite($message, $mediaType)) {
                return $httpMessageConverter->write($message, $mediaType);
            }
        }

        throw InvalidArgumentException::create("Can't write response for {$mediaType->getType()}, no suitable converter found");
    }

    /**
     * @inheritDoc
     */
    public function read(ServerRequestInterface $request, MediaType $mediaType): MessageBuilder
    {
        foreach ($this->httpMessageConverters as $httpMessageConverter) {
            if ($httpMessageConverter->canRead($request, $mediaType)) {
                return $httpMessageConverter->read($request, $mediaType);
            }
        }

        throw InvalidArgumentException::create("Can't read request for {$mediaType->getType()}, no suitable converter found");
    }
}