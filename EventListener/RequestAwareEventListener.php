<?php

namespace Pgs\RestfonyBundle\EventListener;

use Pgs\RestfonyBundle\Event\RequestAwareEventInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * ControllerEventListener
 * @author Karol Joński <kjonski@pgs-soft.com>
 */
abstract class RequestAwareEventListener
{
    protected function getControllerMethod(Request $request)
    {
        $controller = $request->attributes->get('_controller');
        if ($controller) {
            $params = explode(':', $controller);
            return $params[1];
        }
        return false;
    }

    public function onAction(RequestAwareEventInterface $event)
    {
        $action = $this->getControllerMethod($event->getRequest());
        $listenerMethodName = sprintf('on%s', ucfirst($action));
        if (method_exists($this, $listenerMethodName)) {
            $this->{$listenerMethodName}($event);
        }
    }
}
