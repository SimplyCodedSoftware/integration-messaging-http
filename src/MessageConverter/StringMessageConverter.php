<?php

namespace SimplyCodedSoftware\IntegrationMessaging\Http\MessageConverter;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SimplyCodedSoftware\IntegrationMessaging\Annotation\ModuleExtensionAnnotation;
use SimplyCodedSoftware\IntegrationMessaging\Config\Annotation\AnnotationModuleExtension;
use SimplyCodedSoftware\IntegrationMessaging\Config\Annotation\AnnotationRegistrationService;
use SimplyCodedSoftware\IntegrationMessaging\Http\HttpMessageConverter;
use SimplyCodedSoftware\IntegrationMessaging\Http\HttpModule;
use SimplyCodedSoftware\IntegrationMessaging\Http\MediaType;
use SimplyCodedSoftware\IntegrationMessaging\Message;
use SimplyCodedSoftware\IntegrationMessaging\Support\MessageBuilder;

/**
 * Class BodyToPayloadMessageConverter
 * @package SimplyCodedSoftware\IntegrationMessaging\Http\MessageConverter
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 * @ModuleExtensionAnnotation()
 */
class StringMessageConverter implements HttpMessageConverter
{
    const CONVERTER_NAME = "bodyToPayloadConverter";

    /**
     * @inheritDoc
     */
    public static function create(AnnotationRegistrationService $annotationRegistrationService): AnnotationModuleExtension
    {
        return new self();
    }

    public function getName(): string
    {
        return HttpModule::MODULE_NAME;
    }

    public function getConfigurationVariables(): array
    {
        return [];
    }

    public function getRequiredReferences(): array
    {
        return [];
    }


    public static function createWithoutConfigurationVariables() : self
    {
        return new self();
    }

    /**
     * @inheritDoc
     */
    public function getConverterName(): string
    {
        return self::CONVERTER_NAME;
    }

    /**
     * @inheritDoc
     */
    public function canWrite(Message $message, MediaType $mediaType): bool
    {
        return is_string($message->getPayload());
    }

    /**
     * @inheritDoc
     */
    public function write(Message $message, MediaType $mediaType): ResponseInterface
    {
        return new Response(200, [
            self::CONTENT_TYPE => $mediaType->getType()
        ], $message->getPayload());
    }

    /**
     * @inheritDoc
     */
    public function canRead(ServerRequestInterface $request, MediaType $mediaType): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function read(ServerRequestInterface $request, MediaType $mediaType): MessageBuilder
    {
        $body = (string)$request->getBody();

        if (!$body) {
            $body = $mediaType->getType();
        }

        return MessageBuilder::withPayload($body);
    }
}