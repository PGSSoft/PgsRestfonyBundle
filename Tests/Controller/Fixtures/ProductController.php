<?php

namespace Pgs\RestfonyBundle\Tests\Controller\Fixtures;

use FOS\RestBundle\Request\ParamFetcher;
use Pgs\RestfonyBundle\Controller\RestController;
use Symfony\Component\HttpFoundation\Request;

class ProductController extends RestController
{
    public function cgetAction(Request $request, ParamFetcher $paramFetcher)
    {
        return parent::getList($request, $paramFetcher);
    }

    public function getAction(Request $request, $id)
    {
        return parent::getOne($request, $id);
    }

    public function postAction(Request $request)
    {
        return parent::post($request);
    }

    public function putAction(Request $request, $id)
    {
        return parent::put($request, $id);
    }

    public function deleteAction(Request $request, $id)
    {
        return parent::delete($request, $id);
    }

    public function patchPriceAction(ParamFetcher $paramFetcher, $id)
    {
        $value = $paramFetcher->get('price');

        return parent::patch($id, 'price', $value);
    }

    public function patchPrice2Action(ParamFetcher $paramFetcher, $id)
    {
        $value = $paramFetcher->get('price2');

        return parent::patch($id, 'price2', $value);
    }

    public function getNewAction(Request $request)
    {
        return parent::newForm($request);
    }

    public function getEditAction(Request $request, $id)
    {
        return parent::editForm($request, $id);
    }
}
