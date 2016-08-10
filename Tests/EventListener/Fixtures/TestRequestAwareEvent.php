<?php

namespace Pgs\RestfonyBundle\Tests\EventListener\Fixtures;

use Pgs\RestfonyBundle\Event\RequestAwareEventInterface;

/**
 * TestRequestAwareEvent
 * @author Karol Joński <kjonski@pgs-soft.com>
 */
class TestRequestAwareEvent implements RequestAwareEventInterface
{
    public function getRequest()
    {
    }

    public function onTestAction()
    {
    }
}
