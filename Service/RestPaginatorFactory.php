<?php

namespace Pgs\RestfonyBundle\Service;

use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Knp\Component\Pager\Pagination\AbstractPagination;
use Knp\Component\Pager\PaginatorInterface;
use Pgs\RestfonyBundle\Model\RestPaginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @author Michał Sikora
 */
class RestPaginatorFactory
{
    /**
     * @var PaginatorInterface
     */
    private $paginator;

    /**
     * @var RelationPaginatorFactory
     */
    private $relationPaginatorFactory;

    /**
     * @param PaginatorInterface       $paginator
     * @param RelationPaginatorFactory $relationPaginatorFactory
     */
    public function __construct(PaginatorInterface $paginator, RelationPaginatorFactory $relationPaginatorFactory)
    {
        $this->paginator = $paginator;
        $this->relationPaginatorFactory = $relationPaginatorFactory;
    }

    /**
     * @param QueryBuilder          $query
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param string                $moduleName
     *
     * @return RestPaginator
     */
    public function create(QueryBuilder $query, Request $request, ParamFetcherInterface $paramFetcher, $moduleName)
    {
        try {
            $page = $paramFetcher->get('page');
            $limit = $paramFetcher->get('limit');
            /** @var AbstractPagination $paginationView */
            $paginationView =  $this->paginator->paginate($query, $page, $limit);

            return new RestPaginator(
                $page,
                $paginationView,
                $this->relationPaginatorFactory->create($request, $paginationView, $moduleName)
            );
        } catch (ORMException $ex) {
            throw new BadRequestHttpException();
        }
    }
}
