<?php

namespace Pgs\RestfonyBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Pgs\RestfonyBundle\Repository\RestRepository;

class AbstractManager implements ManagerInterface
{
    protected $objectManager;

    protected $class;

    /**
     * @var RestRepository
     */
    protected $repository;

    public function __construct(EntityManagerInterface $objectManager, $class)
    {
        $this->objectManager = $objectManager;
        $this->repository = $objectManager->getRepository($class);

        $metadata = $objectManager->getClassMetadata($class);
        $this->class = $metadata->getName();
    }

    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    public function findAll()
    {
        return $this->getRepository()->findAll();
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->getRepository()->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria)
    {
        return $this->getRepository()->findOneBy($criteria);
    }

    public function getClassName()
    {
        return $this->getRepository()->getClassName();
    }

    public function merge($object, $flush = false)
    {
        $result = $this->getEntityManager()->merge($object);

        if ($flush) {
            $this->getEntityManager()->flush();
        }

        return $result;
    }

    public function persist($object, $flush = false)
    {
        $this->getEntityManager()->persist($object);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove($object, $flush = false)
    {
        $this->getEntityManager()->remove($object);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getEntityManager()
    {
        return $this->objectManager;
    }

    public function getRepository()
    {
        return $this->repository;
    }

    public function create()
    {
        $className = $this->class;

        return new $className();
    }

    public function createList($alias, $idAlias, $hydrationMode = Query::HYDRATE_OBJECT)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        /* @var $qb QueryBuilder */
        return $qb
            ->from($this->getRepository()->getClassName(), $alias, $idAlias)
            ->select($alias)
            ->getQuery()
            ->getResult($hydrationMode);
    }

    public function getListQuery(array $sortConfiguration, $sortQuery)
    {
        return $this->repository->getListQuery($sortConfiguration, $sortQuery);
    }
}
