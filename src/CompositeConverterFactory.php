<?php

namespace SimplyCodedSoftware\IntegrationMessaging\Http;

use SimplyCodedSoftware\IntegrationMessaging\Config\ConfigurationException;
use SimplyCodedSoftware\IntegrationMessaging\Http\MessageConverter\StringMessageConverter;

/**
 * Class MessageConverterFactory
 * @package SimplyCodedSoftware\IntegrationMessaging\Http
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class CompositeConverterFactory
{
    /**
     * @var array|HttpMessageConverter[]
     */
    private $httpMessageConverters;

    /**
     * MessageConverterFactory constructor.
     * @param array|HttpMessageConverter[] $httpMessageConverters
     */
    public function __construct(array $httpMessageConverters)
    {
        $this->httpMessageConverters = $httpMessageConverters;
    }

    /**
     * @param string[] $converterNames
     *
     * @return HttpMessageConverter
     * @throws ConfigurationException
     * @throws \SimplyCodedSoftware\IntegrationMessaging\MessagingException
     */
    public function getMessageConvertersWithNames(array $converterNames) : HttpMessageConverter
    {
        $messageConverters = [];

        foreach ($converterNames as $converterName) {
            $converter = $this->getConverterWithName($converterName);

            if (!$converter) {
                throw ConfigurationException::create("No converter found with name {$converterName}");
            }

            $messageConverters[] = $converter;
        }

        if ($this->canUseDefaultConverter($converterNames)) {
            $messageConverters[] = StringMessageConverter::createWithoutConfigurationVariables();
        }

        return CompositeHttpMessageConverter::createWithConverters($messageConverters);
    }

    /**
     * @param array $converterNames
     * @return bool
     */
    private function canUseDefaultConverter(array $converterNames): bool
    {
        return empty($converterNames);
    }

    /**
     * @param string $converterName
     * @return null|HttpMessageConverter
     */
    private function getConverterWithName(string $converterName)
    {
        foreach ($this->httpMessageConverters as $httpMessageConverter) {
            if ($httpMessageConverter->getConverterName() === $converterName) {
                return $httpMessageConverter;
                break;
            }
        }

        return null;
    }
}