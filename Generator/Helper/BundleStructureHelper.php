<?php

namespace Pgs\RestfonyBundle\Generator\Helper;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class BundleStructureHelper
{
    protected $bundleRoot;
    protected $bundleNamespace;
    protected $entityClass;

    public function __construct(BundleInterface $bundle, $entityClass)
    {
        $this->bundleRoot = $bundle->getPath();
        $this->bundleNamespace = $bundle->getNamespace();
        $this->entityClass = $entityClass;
    }

    public function __call($name, $args)
    {
        $matches = [];
        preg_match('/^get([A-Z][a-z]+)((Full)?[A-Z][a-z]+)$/', $name, $matches);
        if (empty($matches)) {
            throw new \Exception('Unsupported method: ' . $name);
        }
        $category = $matches[1];
        $type = $matches[2];

        $method = 'get' . $type;
        if (!method_exists($this, $method)) {
            throw new \Exception('Unsupported method: ' . $name);
        }
        return $this->$method($category, $args);
    }

    protected function getClassSuffix($suffix)
    {
        return $suffix === 'Entity' ? '' : $suffix;
    }

    protected function getFullClass($category, $args)
    {
        return $this->getNamespace($category, $args) . '\\' . $this->entityClass . $this->getClassSuffix($category);
    }

    protected function getClass($category)
    {
        return basename($this->entityClass . $this->getClassSuffix($category));
    }

    protected function getNamespace($category)
    {
        return $this->bundleNamespace . '\\' . $category;
    }

    protected function getFullFilename($category, $args)
    {
        $filename = $args[0] ?? str_replace('\\', '/', $this->entityClass) . $this->getClassSuffix($category) . '.php';
        return $this->getDirname($category, $args) . '/' . $filename;
    }

    protected function getFilename($category, $args)
    {
        return basename($args[0] ?? str_replace('\\', '/', $this->entityClass) . $this->getClassSuffix($category) . '.php');
    }

    protected function getDirname($category)
    {
        return $this->bundleRoot . '/' . $category;
    }

    public function camelToUnderscore($string)
    {
        return Container::underscore($string);
    }

    public function underscoreToCamel($string)
    {
        return Container::camelize($string);
    }

    public function getBundleNamespace()
    {
        return $this->bundleNamespace;
    }
}
