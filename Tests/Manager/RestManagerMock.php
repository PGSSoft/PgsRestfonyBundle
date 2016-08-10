<?php
/**
 * Created by PhpStorm.
 * User: lgroblewicz
 * Date: 2015-03-19
 * Time: 18:34.
 */

namespace Pgs\RestfonyBundle\Tests\Manager;

use Pgs\RestfonyBundle\Manager\AbstractManager;
use Pgs\RestfonyBundle\Repository\RestRepository;

class RestManagerMock extends AbstractManager
{
    public function __construct(RestRepository $repository)
    {
        $this->repository = $repository;
    }
}
