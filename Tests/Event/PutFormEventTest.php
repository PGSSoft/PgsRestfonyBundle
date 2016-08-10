<?php

namespace Pgs\RestfonyBundle\Event;

use Pgs\RestfonyBundle\Tests\Controller\RestProphecyTestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Michał Sikora
 */
class PutFormEventTest extends RestProphecyTestCase
{
    /**
     * @var PutFormEvent
     */
    private $event;

    public function setUp()
    {
        $this->event = new PutFormEvent($this->getFormMock(false), new Request());
    }

    /**
     * @test
     */
    public function itShouldRetrieveForm()
    {
        $result = $this->event->getForm();
        $this->assertInstanceOf(FormInterface::class, $result);
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
