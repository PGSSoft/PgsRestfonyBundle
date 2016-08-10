<?php

namespace Pgs\RestfonyBundle\Service;

use Doctrine\Common\Util\Inflector;
use Knp\Component\Pager\Pagination\AbstractPagination;
use Pgs\RestfonyBundle\Model\RelationPaginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author Michał Sikora
 */
class RelationPaginatorFactory
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param Request            $request
     * @param AbstractPagination $paginationView
     * @param string             $moduleName
     *
     * @return RelationPaginator
     */
    public function create(Request $request, AbstractPagination $paginationView, $moduleName)
    {
        $first = $this->getFirstLink($request, $moduleName);
        $self = $this->getSelfLink($request, $moduleName);
        $lastPage = ceil($paginationView->getTotalItemCount() / $paginationView->getItemNumberPerPage());
        $last = $this->getLastLink($request, $moduleName, $lastPage);

        return new RelationPaginator($first, $last, $self);
    }

    /**
     * @param Request $request
     * @param string  $moduleName
     *
     * @return string
     */
    protected function getFirstLink(Request $request, $moduleName)
    {
        $parameters = $request->query->all();
        $parameters['page'] = 1;

        return $this->router->generate(sprintf('get_%s', Inflector::pluralize($moduleName)), $parameters);
    }

    /**
     * @param Request $request
     * @param string  $moduleName
     *
     * @return string
     */
    protected function getSelfLink(Request $request, $moduleName)
    {
        return $this->router->generate(sprintf('get_%s', Inflector::pluralize($moduleName)), $request->query->all());
    }

    /**
     * @param Request $request
     * @param string  $moduleName
     * @param int     $lastPage
     *
     * @return string
     */
    protected function getLastLink(Request $request, $moduleName, $lastPage)
    {
        $parameters = $request->query->all();
        $parameters['page'] = $lastPage;

        return $this->router->generate(sprintf('get_%s', Inflector::pluralize($moduleName)), $parameters);
    }
}
