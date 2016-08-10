<?php
/**
 * Created by PhpStorm.
 * User: lgroblewicz
 * Date: 2015-03-19
 * Time: 18:38.
 */

namespace Pgs\RestfonyBundle\Tests\Manager;

use Doctrine\ORM\QueryBuilder;
use Pgs\RestfonyBundle\Repository\RestRepository;

class RestRepositoryMock extends RestRepository
{
    /**
     * @return QueryBuilder
     */
    public function getBaseQuery()
    {
    }
}
