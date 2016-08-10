<?php

namespace Pgs\RestfonyBundle\Tests\EventListener\Fixtures;

use Pgs\RestfonyBundle\EventListener\RequestAwareEventListener;

/**
 * RequestAwareEventListener
 * @author Karol Joński <kjonski@pgs-soft.com>
 */
class TestRequestAwareEventListener extends RequestAwareEventListener
{
    public function onTestAction()
    {
        echo 'onTestActionCalled';
    }
}
