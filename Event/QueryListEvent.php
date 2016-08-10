<?php

namespace Pgs\RestfonyBundle\Event;

use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Michał Sikora
 */
class QueryListEvent extends Event implements RequestAwareEventInterface
{
    /**
     * @var ParamFetcherInterface
     */
    private $paramFetcher;

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var Request
     */
    private $request;

    public function __construct(ParamFetcherInterface $paramFetcher, QueryBuilder $queryBuilder, Request $request)
    {
        $this->paramFetcher = $paramFetcher;
        $this->queryBuilder = $queryBuilder;
        $this->request = $request;
    }

    /**
     * @return ParamFetcherInterface
     */
    public function getParamFetcher()
    {
        return $this->paramFetcher;
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }
}
