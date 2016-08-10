<?php

namespace Pgs\RestfonyBundle\Tests\Service;

use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Knp\Component\Pager\Pagination\AbstractPagination;
use Knp\Component\Pager\PaginatorInterface;
use Pgs\RestfonyBundle\Model\RelationPaginator;
use Pgs\RestfonyBundle\Model\RestPaginator;
use Pgs\RestfonyBundle\Service\RelationPaginatorFactory;
use Pgs\RestfonyBundle\Service\RestPaginatorFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @author Michał Sikora
 */
class RestPaginatorFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldCreateRestPaginatorObjectBasedOnQueryBuilderRequestAndModuleName()
    {
        $paginatorFactory = new RestPaginatorFactory($this->getPaginatorMock1(), $this->getRelationFactoryMock());
        $result = $paginatorFactory->create(
            $this->getQueryBuilderMock(),
            new Request(),
            $this->getParamFetcherMock(),
            'product'
        );
        $this->assertInstanceOf(RestPaginator::class, $result);
        $this->assertSame(1, $result->getPage());
        $this->assertSame(10, $result->getLimit());
        $this->assertSame(2, $result->getPages());
        $this->assertSame([], $result->getItems());
        $this->assertSame(20, $result->getTotal());
        $this->assertSame('products?page=1', $result->getFirstLink());
        $this->assertSame('products?page=1', $result->getLastLink());
        $this->assertSame('products?page=2', $result->getSelfLink());
        $result->setItems([]);
        $result->setTotal(22);
        $result->setPages(3);
        $result->setLimit(5);
        $result->setPage(1);
    }

    /**
     * @test
     */
    public function itShouldCreateThrowBadQueryExceptionInCaseOnBadQueryBuilder()
    {
        $paginatorFactory = new RestPaginatorFactory($this->getPaginatorMock2(), $this->getRelationFactoryMock());

        $this->expectException(BadRequestHttpException::class);

        $paginatorFactory->create($this->getQueryBuilderMock(), new Request(), $this->getParamFetcherMock(), 'product');
    }

    /**
     * @return PaginatorInterface
     */
    private function getPaginatorMock1()
    {
        $paginator = $this->prophesize(PaginatorInterface::class);
        $paginator->paginate(Argument::any(), 1, 10)->willReturn($this->getAbstractPaginationMock());

        return $paginator->reveal();
    }

    /**
     * @return PaginatorInterface
     */
    private function getPaginatorMock2()
    {
        $paginator = $this->prophesize(PaginatorInterface::class);
        $paginator->paginate(Argument::any(), 1, 10)->will(function () {
            throw new ORMException();
        });

        return $paginator->reveal();
    }

    /**
     * @return RelationPaginatorFactory
     */
    private function getRelationFactoryMock()
    {
        $relationFactory = $this->prophesize(RelationPaginatorFactory::class);
        $relationFactory->create(new Request(), Argument::any(), 'product')->willReturn(
            new RelationPaginator('products?page=1', 'products?page=1', 'products?page=2')
        );
        return $relationFactory->reveal();
    }

    /**
     * @return QueryBuilder
     */
    private function getQueryBuilderMock()
    {
        $relationFactory = $this->prophesize(QueryBuilder::class);

        return $relationFactory->reveal();
    }

    /**
     * @return ParamFetcherInterface
     */
    private function getParamFetcherMock()
    {
        $paramFetcher = $this->prophesize(ParamFetcherInterface::class);
        $paramFetcher->get('page')->willReturn(1);
        $paramFetcher->get('limit')->willReturn(10);

        return $paramFetcher->reveal();
    }

    /**
     * @return AbstractPagination
     */
    private function getAbstractPaginationMock()
    {
        $paginationView = $this->prophesize(AbstractPagination::class);
        $paginationView->getItemNumberPerPage()->willReturn(10);
        $paginationView->getTotalItemCount()->willReturn(20);
        $paginationView->getItems()->willReturn([]);

        return $paginationView->reveal();
    }
}
