<?php

namespace Pgs\RestfonyBundle\Tests\Manager;

class DummyQueryBuilder
{
    /**
     * @param string $name
     * @param string $arguments
     * @return DummyQueryBuilder
     */
    public function __call($name, $arguments)
    {
        return method_exists($this, $name) ? $this->$name(...$arguments) : $this;
    }

    /**
     * @return string
     */
    public function getMyName()
    {
        return 'classPretendingToBeQueryBuilder';
    }
}
