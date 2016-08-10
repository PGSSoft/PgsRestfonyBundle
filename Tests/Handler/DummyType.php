<?php

namespace Pgs\RestfonyBundle\Tests\Handler;

class DummyType
{
    /**
     * @var string
     */
    private $typeName;

    /**
     * @param string $typeName
     */
    public function __construct($typeName)
    {
        $this->typeName = $typeName;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->typeName;
    }
}
