<?php
/**
 * RequestAwareEvent
 * @author Karol Joński <kjonski@pgs-soft.com>
 */

namespace Pgs\RestfonyBundle\Event;

interface RequestAwareEventInterface
{
    public function getRequest();
}
