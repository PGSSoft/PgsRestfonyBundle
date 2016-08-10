<?php

namespace Pgs\RestfonyBundle\Event;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Pgs\RestfonyBundle\Model\RestPaginator;
use Symfony\Component\EventDispatcher\Event;

/**
 * @author Michał Sikora
 */
class ResultListEvent extends Event
{
    /**
     * @var ParamFetcherInterface
     */
    private $paramFetcher;

    /**
     * @var RestPaginator
     */
    private $result;

    public function __construct(ParamFetcherInterface $paramFetcher, RestPaginator $result)
    {
        $this->paramFetcher = $paramFetcher;
        $this->result = $result;
    }

    /**
     * @return ParamFetcherInterface
     */
    public function getParamFetcher()
    {
        return $this->paramFetcher;
    }

    /**
     * @return RestPaginator
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param RestPaginator $result
     *
     * @return $this
     */
    public function setResult(RestPaginator $result)
    {
        $this->result = $result;
    }
}
