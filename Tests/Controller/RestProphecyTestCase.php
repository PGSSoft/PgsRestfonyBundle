<?php

namespace Pgs\RestfonyBundle\Tests\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\ViewHandler;
use Pgs\RestfonyBundle\Controller\RestManager;
use Pgs\RestfonyBundle\Doctrine\FilterQueryBuilder;
use Pgs\RestfonyBundle\Form\Factory\RestFormFactory;
use Pgs\RestfonyBundle\Manager\ManagerInterface;
use Pgs\RestfonyBundle\Model\RestPaginator;
use Pgs\RestfonyBundle\Service\RestPaginatorFactory;
use Pgs\RestfonyBundle\Tests\Controller\Fixtures\Product;
use Pgs\RestfonyBundle\Tests\Controller\Fixtures\ProductController;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @author Michał Sikora
 */
class RestProphecyTestCase extends TestCase
{
    protected function createInstance($moduleName, array $events = [], $formSubmit = false)
    {
        return new ProductController(
            $this->getRestManagerMock($moduleName, $formSubmit),
            $this->getFilterQueryBuilderMock(),
            $this->getRestPaginatorFactoryMock(),
            $this->getEventDispatcherMock($events),
            $this->getViewHandlerMock()
        );
    }

    /**
     * @param string $moduleName
     * @param bool   $formSubmit
     *
     * @return RestManager
     */
    protected function getRestManagerMock($moduleName, $formSubmit = false)
    {
        $restManager = $this
            ->prophesize(RestManager::class);

        $restManager->getManager()->willReturn($this->getManagerMock([], 'id'));
        $restManager->getRestFilterFactory()->willReturn($this->getRestFilterFactoryMock());
        $restManager->getModuleName()->willReturn($moduleName);
        $restManager->getRestFormFactory()->willReturn($this->getRestFormFactoryMock($formSubmit));

        return $restManager->reveal();
    }

    /**
     * @return FilterQueryBuilder
     */
    protected function getFilterQueryBuilderMock()
    {
        return $this
            ->prophesize(FilterQueryBuilder::class)
            ->reveal();
    }

    /**
     * @return RestPaginatorFactory
     */
    protected function getRestPaginatorFactoryMock()
    {
        $restPaginatorFactory = $this
            ->prophesize(RestPaginatorFactory::class);

        $restPaginatorFactory->create(Argument::any(), Argument::any(), Argument::any(), Argument::any())
            ->willReturn($this->restPaginatorMock());

        return $restPaginatorFactory->reveal();
    }

    /**
     * @param array $events
     *
     * @return EventDispatcherInterface
     */
    protected function getEventDispatcherMock(array $events = [])
    {
        $eventDispatcher = $this
            ->prophesize(EventDispatcherInterface::class);

        foreach ($events as $event) {
            $eventDispatcher->dispatch($event, Argument::any())->shouldBeCalled();
        }

        return $eventDispatcher->reveal();
    }

    /**
     * @return ViewHandler
     */
    protected function getViewHandlerMock()
    {
        $viewHandler = $this
            ->prophesize(ViewHandler::class);

        $viewHandler->handle(Argument::any())->willReturn(new JsonResponse(

        ));

        return $viewHandler->reveal();
    }

    /**
     * @param array $arguments
     *
     * @return ParamFetcher
     */
    protected function getParamFetcherMock(array $arguments = [])
    {
        $paramFetcher = $this
            ->prophesize(ParamFetcher::class);

        foreach ($arguments as $key => $argument) {
            $paramFetcher->get($key)->willReturn($argument);
        }

        return $paramFetcher->reveal();
    }

    /**
     * @param array  $sortConfiguration
     * @param string $sortQuery
     *
     * @return ManagerInterface
     */
    protected function getManagerMock($sortConfiguration, $sortQuery)
    {
        $manager = $this
            ->prophesize(ManagerInterface::class);

        $product = $this->getProductMock();
        $manager->create()->willReturn($product);
        $manager->getListQuery($sortConfiguration, $sortQuery)->willReturn($this->getQueryBuilderMock());
        $manager->find(1)->willReturn($product);
        $manager->find(2)->willReturn(null);
        $manager->find(3)->willReturn(new Product());
        $manager->persist(Argument::any(), true)->willReturn(null);
        $manager->merge(Argument::any(), true)->willReturn(null);
        $manager->remove(Argument::any(), true)->willReturn(null);

        return $manager->reveal();
    }

    /**
     * @return QueryBuilder
     */
    protected function getQueryBuilderMock()
    {
        $queryBuilder = $this
            ->prophesize(QueryBuilder::class);

        return $queryBuilder->reveal();
    }

    /**
     * @return RestFormFactory
     */
    protected function getRestFilterFactoryMock()
    {
        $restFilterFactory = $this
            ->prophesize(RestFormFactory::class);

        $restFilterFactory->create()->willReturn($this->getFormFilterMock());

        return $restFilterFactory->reveal();
    }

    /**
     * @param bool $formSubmit
     *
     * @return RestFormFactory
     */
    protected function getRestFormFactoryMock($formSubmit = false)
    {
        $restFormFactory = $this
            ->prophesize(RestFormFactory::class);

        $restFormFactory->create(Argument::any())->willReturn($this->getFormMock($formSubmit));

        return $restFormFactory->reveal();
    }

    /**
     * @return FormInterface
     */
    protected function getFormFilterMock()
    {
        $form = $this
            ->prophesize(FormInterface::class);

        return $form->reveal();
    }

    /**
     * @param bool $formSubmit
     *
     * @return FormInterface
     */
    protected function getFormMock($formSubmit = false)
    {
        $form = $this
            ->prophesize(FormInterface::class);

        if ($formSubmit !== false) {
            $form->submit(Argument::any())->shouldBeCalled();
            $form->isValid()->willReturn(true);
            $form->getData()->willReturn($this->getProductMock());
        }

        return $form->reveal();
    }

    /**
     * @return FormFactoryInterface
     */
    protected function getFormFactoryMock()
    {
        return $this->prophesize(FormFactoryInterface::class)->reveal();
    }

    /**
     * @return FormTypeInterface
     */
    protected function getFormTypeMock()
    {
        return $this->prophesize(FormTypeInterface::class)->reveal();
    }

    /**
     * @return RestPaginator
     */
    protected function restPaginatorMock()
    {
        return $this->prophesize(RestPaginator::class)->reveal();
    }

    /**
     * @return Product
     */
    protected function getProductMock()
    {
        return $this ->prophesize(Product::class)->reveal();
    }

    /**
    * @return ObjectManager
    */
    protected function getObjectManagerMock()
    {
        return $this->prophesize(ObjectManager::class)->reveal();
    }

    /**
     * @return ArrayCollection
     */
    protected function getProductsMock()
    {
        return new ArrayCollection([
            $this->prophesize(Product::class)->reveal(),
            $this->prophesize(Product::class)->reveal(),
            $this->prophesize(Product::class)->reveal(),
        ]);
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManagerMock()
    {
        $em = $this->prophesize(EntityManager::class);

        $th = $this;

        $em->getClassMetadata(Argument::type('string'))->will(function ($args) use ($th) {
            return $th->getEntityMetadataMock(...$args);
        });

        return $em->reveal();
    }

    /**
     * @param string $entityName
     * @param int $id
     * @param array $identifierFieldNames
     * @return ClassMetadata
     */
    protected function getEntityMetadataMock($entityName, $id = 1, array $identifierFieldNames = ['id'])
    {
        $cm = $this->prophesize(ClassMetadata::class);
        $cm->getIdentifierFieldNames()->willReturn($identifierFieldNames);
        $class = Argument::type($entityName);
        $cm->getIdentifierValues($class)->willReturn(['id' => $id]);

        return $cm->reveal();
    }
}
