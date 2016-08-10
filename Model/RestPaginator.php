<?php

namespace Pgs\RestfonyBundle\Model;

use Hateoas\Configuration\Route;
use JMS\Serializer\Annotation as Serializer;
use Hateoas\Configuration\Annotation as Hateoas;
use Knp\Component\Pager\Pagination\AbstractPagination;

/**
 * @Serializer\ExclusionPolicy("all")
 *
 * @Hateoas\Relation(
 *      "first",
 *      href = "expr(object.getFirstLink())",
 *      exclusion = @Hateoas\Exclusion(groups = {"list"})
 * )
 *
 * @Hateoas\Relation(
 *      "self",
 *      href = "expr(object.getSelfLink())",
 *      exclusion = @Hateoas\Exclusion(groups = {"list"})
 * )
 *
 * @Hateoas\Relation(
 *      "last",
 *      href = "expr(object.getLastLink())",
 *      exclusion = @Hateoas\Exclusion(groups = {"list"})
 * )
 *
 * @author Michał Sikora
 */
class RestPaginator
{
    /**
     * @var int
     * @Serializer\Expose
     * @Serializer\Groups({"list"})
     * @Serializer\XmlAttribute
     */
    private $page;

    /**
     * @var int
     * @Serializer\Expose
     * @Serializer\Groups({"list"})
     * @Serializer\XmlAttribute
     */
    private $limit;

    /**
     * @var int
     * @Serializer\Expose
     * @Serializer\Groups({"list"})
     * @Serializer\XmlAttribute
     */
    private $pages;

    /**
     * @var int
     * @Serializer\Expose
     * @Serializer\Groups({"list"})
     */
    private $total;

    /**
     * @var int
     * @Serializer\Expose
     * @Serializer\Groups({"list"})
     */
    private $items = [];

    /**
     * @param $page
     * @param AbstractPagination $paginationView
     * @param RelationPaginator  $relationPaginator
     */
    public function __construct($page, AbstractPagination $paginationView, RelationPaginator $relationPaginator)
    {
        $this->page = $page;
        $this->limit = $paginationView->getItemNumberPerPage();
        $this->total = $paginationView->getTotalItemCount();
        $this->items = $paginationView->getItems();
        $this->pages = (int) ceil($paginationView->getTotalItemCount() / $paginationView->getItemNumberPerPage());
        $this->relationPaginator = $relationPaginator;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return int
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return Route
     */
    public function getFirstLink()
    {
        return $this->relationPaginator->getFirst();
    }

    /**
     * @return Route
     */
    public function getLastLink()
    {
        return $this->relationPaginator->getLast();
    }

    /**
     * @return Route
     */
    public function getSelfLink()
    {
        return $this->relationPaginator->getSelf();
    }

    /**
     * @param int $items
     *
     * @return $this
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

    /**
     * @param int $total
     *
     * @return $this
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * @param int $pages
     *
     * @return $this
     */
    public function setPages($pages)
    {
        $this->pages = $pages;
    }

    /**
     * @param int $limit
     *
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * @param int $page
     *
     * @return $this
     */
    public function setPage($page)
    {
        $this->page = $page;
    }
}
