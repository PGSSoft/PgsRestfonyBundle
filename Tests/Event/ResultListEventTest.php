<?php

namespace Pgs\RestfonyBundle\Event;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Pgs\RestfonyBundle\Model\RestPaginator;
use Pgs\RestfonyBundle\Tests\Controller\RestProphecyTestCase;

/**
 * @author Michał Sikora
 */
class ResultListEventTest extends RestProphecyTestCase
{
    /**
     * @var ResultListEvent
     */
    private $event;

    public function setUp()
    {
        $this->event = new ResultListEvent($this->getParamFetcherMock(), $this->restPaginatorMock());
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
    public function itShouldRetrieveResult()
    {
        $result = $this->event->getResult();
        $this->assertInstanceOf(RestPaginator::class, $result);
    }

    /**
     * @test
     */
    public function itShouldSetNewResult()
    {
        $this->event->setResult($this->getNewRestPaginator());
        $result = $this->event->getResult();
        $this->assertInstanceOf(RestPaginator::class, $result);
    }

    /**
     * @return RestPaginator
     */
    private function getNewRestPaginator()
    {
        $restPaginator = $this->prophesize(RestPaginator::class);

        return $restPaginator->reveal();
    }
}
