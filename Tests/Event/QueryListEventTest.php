<?php

namespace Pgs\RestfonyBundle\Event;

use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Pgs\RestfonyBundle\Tests\Controller\RestProphecyTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Michał Sikora
 */
class QueryListEventTest extends RestProphecyTestCase
{
    /**
     * @var QueryListEvent
     */
    private $event;

    public function setUp()
    {
        $this->event = new QueryListEvent($this->getParamFetcherMock(), $this->getQueryBuilderMock(), new Request());
    }

    /**
     * @test
     */
    public function itShouldRetrieveParamFetcher()
    {
        $result = $this->event->getParamFetcher();
        $this->assertInstanceOf(ParamFetcherInterface::class, $result);
    }

    /**
     * @test
     */
    public function itShouldRetrieveQueryBuilder()
    {
        $result = $this->event->getQueryBuilder();
        $this->assertInstanceOf(QueryBuilder::class, $result);
    }

    /**
     * @test
     */
    public function itShouldRetrieveRequest()
    {
        $result = $this->event->getRequest();
        $this->assertInstanceOf(Request::class, $result);
    }
}
