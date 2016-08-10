<?php
/**
 * RequestAwareEventListenerTest
 * @author Karol Joński <kjonski@pgs-soft.com>
 */

namespace Pgs\RestfonyBundle\Tests\EventListener;

use Pgs\RestfonyBundle\Event\RequestAwareEventInterface;
use Pgs\RestfonyBundle\EventListener\RequestAwareEventListener;
use Pgs\RestfonyBundle\Tests\Controller\RestProphecyTestCase;
use Pgs\RestfonyBundle\Tests\EventListener\Fixtures\TestRequestAwareEvent;
use Pgs\RestfonyBundle\Tests\EventListener\Fixtures\TestRequestAwareEventListener;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class RequestAwareEventListenerTest extends RestProphecyTestCase
{
    /**
     * @var RequestAwareEventListener
     */
    private $listener;

    public function setUp()
    {
        $this->listener = new TestRequestAwareEventListener();
    }

    /**
     * @test
     * @dataProvider eventWithRequestWithControllerDataProvider
     * @param RequestAwareEventInterface $event
     * @param string $expectedOutput
     */
    public function itShouldCallExpectedMethod($event, $expectedOutput)
    {
        $this->listener->onAction($event);
        $this->expectOutputString($expectedOutput);
    }

    /**
     * @test
     * @dataProvider eventWithEmptyRequestDataProvider
     * @param RequestAwareEventInterface $event
     * @param string $expectedOutput
     */
    public function itShouldNotCallExpectedMethod($event, $expectedOutput)
    {
        $this->listener->onAction($event);
        $this->expectOutputString($expectedOutput);
    }

    public function eventWithRequestWithControllerDataProvider()
    {
        $expectedOutputString = 'onTestActionCalled';
        $request = new Request();
        $request->attributes = new ParameterBag(['_controller' => 'pgs.rest.controller.test:testAction']);

        $event = $this->getEventMock($request, $expectedOutputString);
        return [
            [
                $event,
                $expectedOutputString
            ]
        ];
    }

    public function eventWithEmptyRequestDataProvider()
    {
        $expectedOutputString = '';
        $request = new Request();

        $event = $this->getEventMock($request, $expectedOutputString);

        return [
            [
                $event,
                $expectedOutputString
            ]
        ];
    }

    private function getEventMock($request, $expectedOutputString)
    {
        $eventStub = $this->prophesize(TestRequestAwareEvent::class);
        $eventStub->getRequest()->willReturn($request);
        $eventStub->onTestAction()->will(function () use ($expectedOutputString) {
            echo $expectedOutputString;
        });
        return $eventStub->reveal();
    }
}
