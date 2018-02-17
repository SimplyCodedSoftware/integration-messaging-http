<?php

namespace Fixture;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SimplyCodedSoftware\IntegrationMessaging\Config\ConfigurationVariableRetrievingService;
use SimplyCodedSoftware\IntegrationMessaging\Config\ModuleConfigurationExtension;
use SimplyCodedSoftware\IntegrationMessaging\Http\HttpMessageConverter;
use SimplyCodedSoftware\IntegrationMessaging\Http\MediaType;
use SimplyCodedSoftware\IntegrationMessaging\Message;
use SimplyCodedSoftware\IntegrationMessaging\Support\MessageBuilder;

/**
 * Class DumbHttpMessageConverter
 * @package Fixture
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class DumbHttpMessageConverter implements HttpMessageConverter
{
    /**
     * @var string
     */
    private $converterName;
    /**
     * @var bool
     */
    private $canWrite;
    /**
     * @var bool
     */
    private $canRead;
    /**
     * @var MessageBuilder
     */
    private $readResult;
    /**
     * @var ResponseInterface
     */
    private $writeResult;

    /**
     * DumbHttpMessageConverter constructor.
     * @param string $converterName
     * @param bool $canWrite
     * @param bool $canRead
     */
    private function __construct(string $converterName, bool $canWrite, bool $canRead)
    {
        $this->converterName = $converterName;
        $this->canWrite = $canWrite;
        $this->canRead = $canRead;
    }

    /**
     * @inheritDoc
     */
    public static function create(ConfigurationVariableRetrievingService $configurationVariableRetrievingService): ModuleConfigurationExtension
    {
        return self::createNotWritableAndReadable("test");
    }

    /**
     * @param string $converterName
     * @param bool $canWrite
     * @param bool $canRead
     * @return DumbHttpMessageConverter
     */
    public static function createWith(string $converterName, bool $canWrite, bool $canRead) : self
    {
        return new self($converterName, $canWrite, $canRead);
    }

    /**
     * @param string $converterName
     * @return DumbHttpMessageConverter
     */
    public static function createNotWritableAndReadable(string $converterName) : self
    {
        return new self($converterName, false, false);
    }

    /**
     * @param string $convertName
     * @return DumbHttpMessageConverter
     */
    public static function createWritableAndReadable(string $convertName) : self
    {
        return new self($convertName, true, true);
    }

    public function withReadResult(MessageBuilder $message) : self
    {
        $this->readResult = $message;

        return $this;
    }

    public function withWriteResult(ResponseInterface $response) : self
    {
        $this->writeResult = $response;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getConverterName(): string
    {
        return $this->converterName;
    }

    /**
     * @inheritDoc
     */
    public function canWrite(Message $message, MediaType $mediaType): bool
    {
        return $this->canWrite;
    }

    /**
     * @inheritDoc
     */
    public function canRead(ServerRequestInterface $request, MediaType $mediaType): bool
    {
        return $this->canRead;
    }

    /**
     * @inheritDoc
     */
    public function write(Message $message, MediaType $mediaType): ResponseInterface
    {
        return $this->writeResult;
    }

    /**
     * @inheritDoc
     */
    public function read(ServerRequestInterface $request, MediaType $mediaType): MessageBuilder
    {
        return $this->readResult;
    }
}