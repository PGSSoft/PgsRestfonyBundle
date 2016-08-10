<?php

namespace Pgs\RestfonyBundle\Tests\Repository\Fixtures;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\QueryBuilder;
use Pgs\RestfonyBundle\Repository\RestRepository;

class ProductRepository extends RestRepository
{
    /**
     * @return QueryBuilder
     */
    public function getBaseQuery()
    {
        $config = new Configuration();
        $config->setEntityNamespaces(array('SymfonyTestsDoctrine' => 'Symfony\Bridge\Doctrine\Tests\Fixtures'));
        $config->setAutoGenerateProxyClasses(true);
        $config->setProxyDir(\sys_get_temp_dir());
        $config->setProxyNamespace('SymfonyTests\Doctrine');
        $config->setMetadataDriverImpl(new AnnotationDriver(new AnnotationReader()));
        $config->setQueryCacheImpl(new ArrayCache());
        $config->setMetadataCacheImpl(new ArrayCache());
        $params = array(
            'driver' => 'pdo_sqlite',
            'memory' => true,
        );
        $em = EntityManager::create($params, $config);

        return $em->createQueryBuilder()->select('p')->from('Product', 'p');
    }
}
