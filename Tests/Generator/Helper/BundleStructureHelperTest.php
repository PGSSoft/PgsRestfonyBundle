<?php

namespace Pgs\RestfonyBundle\Tests\Generator\Helper;

use Doctrine\Bundle\DoctrineCacheBundle\Tests\TestCase;
use Pgs\RestfonyBundle\Generator\Helper\BundleStructureHelper;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BundleStructureHelperTest extends TestCase
{
    /**
     * @var BundleStructureHelper
     */
    protected $helper;

    public function setUp()
    {
        $this->helper = new BundleStructureHelper(self::getBundleMock(), 'DummyEntity');
    }

    public function testGetControllerFullFilename()
    {
        $this->assertSame(
            '/dummy/path/to/DummyBundle/Controller/DummyEntityController.php',
            $this->helper->getControllerFullFilename()
        );
    }

    public function testGetResourcesFullFilenameCustom()
    {
        $this->assertSame(
            '/dummy/path/to/DummyBundle/Resources/sub/dir/file.yml',
            $this->helper->getResourcesFullFilename('sub/dir/file.yml')
        );
    }

    public function testGetEntityFilename()
    {
        $this->assertSame('DummyEntity.php', $this->helper->getEntityFilename());
    }

    public function testGetServiceFilename()
    {
        $this->assertSame('DummyEntityService.php', $this->helper->getServiceFilename());
    }

    public function testGetManagerFullClass()
    {
        $this->assertSame('DummyAndCo\DummyBundle\Manager\DummyEntityManager', $this->helper->getManagerFullClass());
    }

    public function testGetEntityClass()
    {
        $this->assertSame('DummyEntity', $this->helper->getEntityClass());
    }

    public function testExceptionOnNotSupportedMethod()
    {
        $this->expectException(\Exception::class);
        $this->helper->callNotExistingMethod();
    }

    public function testExceptionForNonExistingMethod()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unsupported method: getUnsupportedMethod');

        $this->helper->getUnsupportedMethod();
    }

    public function testCamelToUnderscore()
    {
        $this->assertSame('camel_case_string', $this->helper->camelToUnderscore('CamelCaseString'));
    }

    public function testUnderscoreToCamel()
    {
        $this->assertSame('UnderscoreString', $this->helper->underscoreToCamel('underscore_string'));
    }

    public function testGetBundleNamespace()
    {
        $this->assertSame('DummyAndCo\DummyBundle', $this->helper->getBundleNamespace());
    }

    protected function getBundleMock()
    {
        $mock = $this->getMockBuilder(Bundle::class)
            ->setMethods(['getPath', 'getNamespace'])
            ->getMock();

        $mock->method('getPath')->willReturn('/dummy/path/to/DummyBundle');
        $mock->method('getNamespace')->willReturn('DummyAndCo\DummyBundle');

        return $mock;
    }
}
