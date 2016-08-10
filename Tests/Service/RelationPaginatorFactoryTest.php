<?php

namespace Pgs\RestfonyBundle\Tests\Service;

use Knp\Component\Pager\Pagination\AbstractPagination;
use Pgs\RestfonyBundle\Model\RelationPaginator;
use Pgs\RestfonyBundle\Service\RelationPaginatorFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author Michał Sikora
 */
class RelationPaginatorFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldCreateRelationPaginatorObjectBasedOnRequestPaginationViewAndModuleName()
    {
        $factory = new RelationPaginatorFactory($this->getRouterMock());
        $result = $factory->create(new Request(['page' => 2]), $this->getAbstractPaginationMock(), 'product');
        $this->assertInstanceOf(RelationPaginator::class, $result);
        $this->assertSame("products?page=1", $result->getFirst());
        $this->assertSame("products?page=2", $result->getSelf());
        $this->assertSame("products?page=3", $result->getLast());
    }

    /**
     * @return RouterInterface
     */
    private function getRouterMock()
    {
        $router = $this->prophesize(RouterInterface::class);

        $request = new Request(['page' => 2]);
        $router->generate('get_products', $request->query->all())->willReturn('products?page=2');

        $request = new Request(['page' => 1]);
        $router->generate('get_products', $request->query->all())->willReturn('products?page=1');

        $request = new Request(['page' => 3]);
        $router->generate('get_products', $request->query->all())->willReturn('products?page=3');

        return $router->reveal();
    }

    /**
     * @return AbstractPagination
     */
    private function getAbstractPaginationMock()
    {
        $paginator = $this->prophesize(AbstractPagination::class);
        $paginator->getTotalItemCount()->willReturn(30);
        $paginator->getItemNumberPerPage()->willReturn(10);

        return $paginator->reveal();
    }
}
