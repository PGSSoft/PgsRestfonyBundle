<?php

namespace Pgs\RestfonyBundle\Tests\Form\DataTransformer;

use Pgs\RestfonyBundle\Form\DataTransformer\RestCollectionTransformer;
use Pgs\RestfonyBundle\Tests\Controller\RestProphecyTestCase;
use Pgs\RestfonyBundle\Tests\DataTransformer\Fixtures\Product;

class RestCollectionTransformerTest extends RestProphecyTestCase
{

    /**
     * @var RestCollectionTransformer
     */
    private $restCollectionTransformer;

    public function setUp()
    {
        $this->restCollectionTransformer = new RestCollectionTransformer($this->getEntityManagerMock(), Product::class);
    }

    /**
     * @test
     */
    public function itShouldRetrieveRestCollectionTransformer()
    {
        $this->assertInstanceOf(RestCollectionTransformer::class, $this->restCollectionTransformer);
    }

    /**
     * @test
     */
    public function itShouldReturnArrayOfEntitiesIdsTest()
    {
        $this->assertInternalType('array', $this->restCollectionTransformer->transform($this->getProductsMock()));
    }
}
