<?php

namespace AppBundle\Collections;

use AppBundle\File;
use JMS\Serializer\Annotation as Serializer;

class FileCollection
{
    /**
     * @var file[]
     *
     * @Serializer\XmlList(inline=false, entry="file")
     * @Serializer\Type("array<AppBundle\Entity\File>")
     */
    public $files = [];

    public function __construct($files = array())
    {
        $this->files = $files;
    }
}