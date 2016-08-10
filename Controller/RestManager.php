<?php

namespace Pgs\RestfonyBundle\Controller;

use Pgs\RestfonyBundle\Form\Factory\RestFormFactory;
use Pgs\RestfonyBundle\Manager\ManagerInterface;

/**
 * @author Michał Sikora
 */
class RestManager
{
    /**
     * @var string
     */
    private $moduleName;

    /**
     * @var ManagerInterface
     */
    private $manager;

    /**
     * @var RestFormFactory
     */
    private $restFormFactory;

    /**
     * @var RestFormFactory
     */
    private $restFilterFactory;

    /**
     * @param $moduleName
     * @param ManagerInterface $manager
     * @param RestFormFactory  $restFormFactory
     * @param RestFormFactory  $restFilterFactory
     */
    public function __construct(
        $moduleName,
        ManagerInterface $manager,
        RestFormFactory $restFormFactory,
        RestFormFactory $restFilterFactory
    ) {
        $this->moduleName = $moduleName;
        $this->manager = $manager;
        $this->restFormFactory = $restFormFactory;
        $this->restFilterFactory = $restFilterFactory;
    }

    /**
     * @return ManagerInterface
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @return RestFormFactory
     */
    public function getRestFormFactory()
    {
        return $this->restFormFactory;
    }

    /**
     * @return RestFormFactory
     */
    public function getRestFilterFactory()
    {
        return $this->restFilterFactory;
    }

    /**
     * @return string
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }
}
