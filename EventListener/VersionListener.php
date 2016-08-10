<?php

namespace Pgs\RestfonyBundle\EventListener;

use FOS\RestBundle\View\ConfigurableViewHandlerInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class VersionListener
{
    private $viewHandler;
    private $regex;
    private $version = false;

    public function __construct(ViewHandlerInterface $viewHandler)
    {
        $this->viewHandler = $viewHandler;
    }

    /**
     * Gets the version.
     *
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Sets the regex.
     *
     * @param string $regex
     */
    public function setRegex($regex)
    {
        $this->regex = $regex;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $version = $request->request->get('version');

        if ($version) {
            $this->version = $version;
            $request->attributes->set('version', $this->version);

            if ($this->viewHandler instanceof ConfigurableViewHandlerInterface) {
                $this->viewHandler->setExclusionStrategyVersion($this->version);
            }
        }
    }
}
