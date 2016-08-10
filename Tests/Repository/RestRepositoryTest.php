<?php

namespace Pgs\RestfonyBundle\Tests\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Pgs\RestfonyBundle\Tests\Repository\Fixtures\ProductRepository;
use PHPUnit\Framework\TestCase;

/**
 * @author Michał Sikora
 */
class RestRepositoryTest extends TestCase
{
    /**
     * @test
     * @dataProvider getSorts
     *
     * @param array $conf
     * @param string $query
     * @param string $stringContains
     */
    public function itShouldGenerateListQueryFromBaseQueryWithSorting($conf, $query, $stringContains)
    {
        $repository = new ProductRepository($this->getEntityManagerMock(), $this->getClassMetadataMock());
        $query = $repository->getListQuery($conf, $query);

        $this->assertContains($stringContains, $query->getDQL());
    }

    /**
     * @return array
     */
    public function getSorts()
    {
        return [
            [['id' => 'id'], 'id', 'id asc'],
            [['id' => 'id'], '-id', 'id desc'],
            [['id' => 'id', 'name' => 'name'], 'name|-id', 'name asc, id desc']
        ];
    }

    /**
     * @return ClassMetadata
     */
    private function getClassMetadataMock()
    {
        $classMetadata = $this->prophesize(ClassMetadata::class);

        return $classMetadata->reveal();
    }

    /**
     * @return EntityManager
     */
    private function getEntityManagerMock()
    {
        $em = $this->prophesize(EntityManager::class);

        return $em->reveal();
    }
}
