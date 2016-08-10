<?php

namespace Pgs\RestfonyBundle\Model;

/**
 * @author Michał Sikora
 */
class RelationPaginator
{
    /**
     * @var string
     */
    private $first;

    /**
     * @var string
     */
    private $last;

    /**
     * @var string
     */
    private $self;

    /**
     * @param string $first
     * @param string $last
     * @param string $self
     */
    public function __construct($first, $last, $self)
    {
        $this->first = $first;
        $this->last = $last;
        $this->self = $self;
    }

    /**
     * @return string
     */
    public function getFirst()
    {
        return $this->first;
    }

    /**
     * @return string
     */
    public function getLast()
    {
        return $this->last;
    }

    /**
     * @return string
     */
    public function getSelf()
    {
        return $this->self;
    }
}
