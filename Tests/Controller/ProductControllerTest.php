<?php

namespace Pgs\RestfonyBundle\Tests\Controller;

use Pgs\RestfonyBundle\RestEvents;
use Pgs\RestfonyBundle\Tests\Controller\Fixtures\ProductController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;

/**
 * @author Michał Sikora
 */
class ProductControllerTest extends RestProphecyTestCase
{
    /**
     * @test
     */
    public function itShouldSetSortConfiguration()
    {
        $controller = $this->createInstance('product', []);
        $controller->setSortConfiguration([]);

        $this->assertInstanceOf(ProductController::class, $controller);
    }

    /**
     * @test
     */
    public function itShouldReturnPaginatedListForCgetAction()
    {
        $controller = $this->createInstance('product', [
            RestEvents::LIST_ACTION_BEFORE_PAGINATION,
            RestEvents::LIST_ACTION_AFTER_PAGINATION,
            RestEvents::HANDLE_VIEW,
        ]);

        $result = $controller->cgetAction(new Request(), $this->getParamFetcherMock([
            'sorts' => 'id',
        ]));

        $this->assertInstanceOf(Response::class, $result);
    }

    /**
     * @test
     */
    public function itShouldRetrieveRecordForGetAction()
    {
        $controller = $this->createInstance('product', [
            RestEvents::GET_ACTION_POST_LOAD,
            RestEvents::HANDLE_VIEW,
        ]);

        $result = $controller->getAction(new Request(), 1);

        $this->assertInstanceOf(Response::class, $result);
    }

    /**
     * @test
     * @covers Pgs\RestfonyBundle\Controller\RestController::findOr404
     */
    public function itShouldThrownExceptionForNotFoundRecordsForGetAction()
    {
        $controller = $this->createInstance('product', []);

        $this->expectException(NotFoundHttpException::class);

        $controller->getAction(new Request(), 2);
    }

    /**
     * @test
     */
    public function itShouldCreateNewRecordFromFormForPostAction()
    {
        $controller = $this->createInstance('product', [
            RestEvents::POST_ACTION_PRE_SUBMIT,
            RestEvents::POST_ACTION_POST_VALIDATION,
            RestEvents::POST_ACTION_POST_PERSIST,
            RestEvents::HANDLE_VIEW,
        ], true);

        $controller->postAction(new Request());
    }

    /**
     * @test
     */
    public function itShouldReturnValidatedFormForNotSubmittedFormForPostAction()
    {
        $controller = $this->createInstance('product', [
            RestEvents::POST_ACTION_PRE_SUBMIT,
            RestEvents::POST_ACTION_VALIDATION_ERROR,
            RestEvents::HANDLE_VIEW,
        ], false);

        $controller->postAction(new Request());
    }

    /**
     * @test
     */
    public function itShouldEditRecordFromFormForPutAction()
    {
        $controller = $this->createInstance('product', [
            RestEvents::PUT_ACTION_PRE_SUBMIT,
            RestEvents::PUT_ACTION_POST_VALIDATION,
            RestEvents::PUT_ACTION_POST_PERSIST,
            RestEvents::HANDLE_VIEW,
        ], true);

        $controller->putAction(new Request(), 1);
    }

    /**
     * @test
     */
    public function itShouldReturnValidatedFormForNotSubmittedFormForPutAction()
    {
        $controller = $this->createInstance('product', [
            RestEvents::PUT_ACTION_PRE_SUBMIT,
            RestEvents::PUT_ACTION_VALIDATION_ERROR,
            RestEvents::HANDLE_VIEW,
        ], false);

        $controller->putAction(new Request(), 1);
    }

    /**
     * @test
     */
    public function itShouldDeleteEntityForDeleteAction()
    {
        $controller = $this->createInstance('product', [
            RestEvents::DELETE_ACTION_PRE_DELETE,
            RestEvents::DELETE_ACTION_POST_DELETE,
            RestEvents::HANDLE_VIEW,
        ], false);

        $controller->deleteAction(new Request(), 1);
    }

    /**
     * @test
     */
    public function itShouldPatchCustomField()
    {
        $controller = $this->createInstance('product', [
            RestEvents::PRE_PATCHED_ACTION,
            RestEvents::POST_PATCHED_ACTION,
            RestEvents::HANDLE_VIEW,
        ], false);

        $controller->patchPriceAction($this->getParamFetcherMock(), 3);
    }

    /**
     * @test
     */
    public function itShouldThrowExceptionWhilePatchingNonAvailableField()
    {
        $controller = $this->createInstance('product', [], false);

        $this->expectException(NoSuchPropertyException::class);

        $controller->patchPrice2Action($this->getParamFetcherMock(), 3);
    }

    /**
     * @test
     */
    public function itShouldDisplayFormViewForNewAction()
    {
        $controller = $this->createInstance('product', [
            RestEvents::NEW_ACTION_LOAD,
            RestEvents::HANDLE_VIEW,
        ], false);

        $controller->getNewAction(new Request());
    }

    /**
     * @test
     */
    public function itShouldDisplayFormViewForEditAction()
    {
        $controller = $this->createInstance('product', [
            RestEvents::EDIT_ACTION_LOAD,
            RestEvents::HANDLE_VIEW,
        ], false);

        $controller->getEditAction(new Request(), 1);
    }
}
