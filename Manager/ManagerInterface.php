<?php

namespace Pgs\RestfonyBundle\Manager;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\Query;

/**
 * Interface ManagerInterface.
 *
 * @author Michał Sikora
 */
interface ManagerInterface extends ObjectRepository
{
    public function getEntityManager();

    public function getRepository();

    public function create();

    public function persist($object, $flush = false);

    public function merge($object, $flush = false);

    public function remove($object, $flush = false);

    public function createList($alias, $idAlias, $hydrationMode = Query::HYDRATE_ARRAY);

    public function getListQuery(array $sortConfiguration, $sortQuery);
}
