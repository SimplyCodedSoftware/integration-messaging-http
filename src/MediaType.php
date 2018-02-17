<?php

namespace SimplyCodedSoftware\IntegrationMessaging\Http;

use SimplyCodedSoftware\IntegrationMessaging\Support\InvalidArgumentException;

/**
 * Class MediaType
 * @package SimplyCodedSoftware\IntegrationMessaging\Http
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class MediaType
{
    const APPLICATION_ATOM_XML_VALUE = "application/atom+xml";
    const APPLICATION_FORM_URLENCODED_VALUE = "application/x-www-form-urlencoded";
    const APPLICATION_JSON_VALUE = "application/json";
    const APPLICATION_JSON_UTF8_VALUE = "application/json;charset=UTF-8";
    const APPLICATION_OCTET_STREAM_VALUE = "application/octet-stream";
    const APPLICATION_PDF_VALUE = "application/pdf";
    const APPLICATION_PROBLEM_JSON_VALUE = "application/problem+json";
    const APPLICATION_PROBLEM_JSON_UTF8_VALUE = "application/problem+json;charset=UTF-8";
    const APPLICATION_PROBLEM_XML_VALUE = "application/problem+xml";
    const APPLICATION_RSS_XML_VALUE = "application/rss+xml";
    const APPLICATION_STREAM_JSON_VALUE = "application/stream+json";
    const APPLICATION_XHTML_XML_VALUE = "application/xhtml+xml";
    const APPLICATION_XML_VALUE = "application/xml";
    const IMAGE_GIF_VALUE = "image/gif";
    const IMAGE_JPEG_VALUE = "image/jpeg";
    const IMAGE_PNG_VALUE = "image/png";
    const MULTIPART_FORM_DATA_VALUE = "multipart/form-data";
    const TEXT_EVENT_STREAM_VALUE = "text/event-stream";
    const TEXT_HTML_VALUE = "text/html";
    const TEXT_MARKDOWN_VALUE = "text/markdown";
    const TEXT_PLAIN_VALUE = "text/plain";
    const TEXT_XML_VALUE = "text/xml";

    /**
     * @var string
     */
    private $type;

    /**
     * MediaType constructor.
     * @param string $type
     */
    private function __construct(string $type)
    {
        $this->initialize($type);
    }

    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return bool
     */
    public function hasType(string $type) : bool
    {
        return $this->getType() == $type;
    }

    /**
     * @param string $type
     * @return MediaType
     */
    public static function createWith(string $type) : self
    {
        return new self($type);
    }

    /**
     * @return MediaType
     */
    public static function createApplicationJson() : self
    {
        return new self(self::APPLICATION_JSON_VALUE);
    }


    /**
     * @param string $type
     * @throws InvalidArgumentException
     */
    private function initialize(string $type)
    {
        $this->type = strtolower($type);
    }
}