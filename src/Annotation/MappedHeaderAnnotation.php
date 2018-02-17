<?php

namespace SimplyCodedSoftware\IntegrationMessaging\Http\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * Class MappedHeaderAnnotation
 * @package SimplyCodedSoftware\IntegrationMessaging\Http\Annotation
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 * @Annotation
 */
class MappedHeaderAnnotation
{
    /**
     * @var string
     * @Required()
     */
    public $fromKey;
    /**
     * @var string
     * @Required()
     */
    public $toKey;
}