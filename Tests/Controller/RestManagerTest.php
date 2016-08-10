<?php

namespace Pgs\RestfonyBundle\Tests\Controller;

use Pgs\RestfonyBundle\Controller\RestManager;
use Pgs\RestfonyBundle\Form\Factory\RestFormFactory;
use Pgs\RestfonyBundle\Manager\ManagerInterface;

/**
 * @author Michał Sikora
 */
class RestManagerTest extends RestProphecyTestCase
{
    /**
     * @var RestManager
     */
    private $restManager;

    public function setUp()
    {
        $this->restManager = new RestManager(
            'product',
            $this->getManagerMock([], ''),
            $this->getRestFormFactoryMock(),
            $this->getRestFilterFactoryMock()
        );
    }

    /**
     * @test
     */
    public function itShouldRetrieveRestManagerObject()
    {
        $this->assertInstanceOf(ManagerInterface::class, $this->restManager->getManager());
    }

    /**
     * @test
     */
    public function itShouldRetrieveRestFormFactory()
    {
        $this->assertInstanceOf(RestFormFactory::class, $this->restManager->getRestFormFactory());
    }

    /**
     * @test
     */
    public function itShouldRetrieveRestFilterFactory()
    {
        $this->assertInstanceOf(RestFormFactory::class, $this->restManager->getRestFilterFactory());
    }

    /**
     * @test
     */
    public function itShouldRetrieveRestModuleName()
    {
        $this->assertSame('product', $this->restManager->getModuleName());
    }
}
