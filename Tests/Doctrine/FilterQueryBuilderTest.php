<?php

namespace Pgs\RestfonyBundle\Tests\Doctrine;

use Doctrine\ORM\QueryBuilder;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use Pgs\RestfonyBundle\Doctrine\FilterQueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Michał Sikora
 */
class FilterQueryBuilderTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldBuildQueryBasedOnBaseQueryAndFilterResults()
    {
        $filterQueryBuilder = new FilterQueryBuilder($this->getFilterBuilderUpdaterMock());

        $filterQueryBuilder->createQueryForListAction(
            $this->getQueryBuilderMock(),
            $this->getFormMock(),
            new Request([ 'filter' => 1 ])
        );
    }

    /**
     * @return FilterBuilderUpdaterInterface
     */
    private function getFilterBuilderUpdaterMock()
    {
        $filterBuilderUpdater = $this->prophesize(FilterBuilderUpdaterInterface::class);
        $filterBuilderUpdater->addFilterConditions(Argument::any(), Argument::any())->shouldBeCalled();

        return $filterBuilderUpdater->reveal();
    }

    /**
     * @return QueryBuilder
     */
    private function getQueryBuilderMock()
    {
        $queryBuilder = $this->prophesize(QueryBuilder::class);

        return $queryBuilder->reveal();
    }

    /**
     * @return FormInterface
     */
    private function getFormMock()
    {
        $form = $this->prophesize(FormInterface::class);
        $form->getName()->willReturn('filter');
        $form->submit(1)->shouldBeCalled();

        return $form->reveal();
    }
}
