<?php

namespace Pgs\RestfonyBundle\Doctrine;

use Doctrine\ORM\QueryBuilder;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Michał Sikora
 */
class FilterQueryBuilder
{
    /**
     * @var FilterBuilderUpdaterInterface
     */
    private $lexikQueryBuilderUpdater;

    /**
     * @param FilterBuilderUpdaterInterface $lexikQueryBuilderUpdater
     */
    public function __construct(FilterBuilderUpdaterInterface $lexikQueryBuilderUpdater)
    {
        $this->lexikQueryBuilderUpdater = $lexikQueryBuilderUpdater;
    }

    /**
     * @param QueryBuilder  $baseQuery
     * @param FormInterface $form
     * @param Request       $request
     */
    public function createQueryForListAction(QueryBuilder $baseQuery, FormInterface $form, Request $request)
    {
        if ($request->query->has($form->getName())) {
            $form->submit($request->query->get($form->getName()));
            $this->lexikQueryBuilderUpdater->addFilterConditions($form, $baseQuery);
        }
    }
}
