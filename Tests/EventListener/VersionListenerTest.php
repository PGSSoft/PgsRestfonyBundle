<?php

namespace Pgs\RestfonyBundle\Tests\EventListener;

use FOS\RestBundle\View\ConfigurableViewHandlerInterface;
use Pgs\RestfonyBundle\EventListener\VersionListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @covers Pgs\RestfonyBundle\EventListener\VersionListener
 */
class VersionListenerTest extends TestCase
{
    const TESTED_VERSION = '2.0';

    /**
     * @test
     */
    public function versionShouldBeSaved()
    {
        // Given
        $versionListener = new VersionListener($this->createMock(ConfigurableViewHandlerInterface::class));
        $versionListener->setRegex('/.*/');

        $request = new Request();
        $request->initialize([], ['version' => self::TESTED_VERSION]);

        $getResponseEvent = new GetResponseEvent($this->createMock(Kernel::class), $request, 'GET');

        // When
        $versionListener->onKernelRequest($getResponseEvent);

        // Then
        $this->assertSame(self::TESTED_VERSION, $versionListener->getVersion());
    }
}
