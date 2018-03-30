<?php

namespace SimplyCodedSoftware\IntegrationMessaging\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use SimplyCodedSoftware\IntegrationMessaging\Config\Annotation\AnnotationModuleExtension;
use SimplyCodedSoftware\IntegrationMessaging\Message;
use SimplyCodedSoftware\IntegrationMessaging\Support\MessageBuilder;

/**
 * Interface HttpMessageConverter
 * @package SimplyCodedSoftware\IntegrationMessaging\Http
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
interface HttpMessageConverter extends AnnotationModuleExtension
{
    public const CONTENT_TYPE = 'content-type';
    public const ACCEPT_TYPE = 'accept';
    
    public const METHOD_TYPE_GET = 'GET';
    public const METHOD_TYPE_POST = 'POST';
    public const METHOD_TYPE_PUT = 'PUT';
    public const METHOD_TYPE_OPTIONS = 'OPTIONS';

    /**
     * @return string
     */
    public function getConverterName() : string;

    /**
     * @param Message $message
     * @param MediaType $mediaType
     * @return bool
     */
    public function canWrite(Message $message, MediaType $mediaType) : bool;

    /**
     * @param ServerRequestInterface $request
     * @param MediaType $mediaType
     * @return bool
     */
    public function canRead(ServerRequestInterface $request, MediaType $mediaType) : bool;

    /**
     * @param Message $message
     * @param MediaType $mediaType
     * @return ResponseInterface
     */
    public function write(Message $message, MediaType $mediaType) : ResponseInterface;

    /**
     * @param ServerRequestInterface $request
     * @param MediaType $mediaType
     * @return MessageBuilder
     */
    public function read(ServerRequestInterface $request, MediaType $mediaType) : MessageBuilder;
}