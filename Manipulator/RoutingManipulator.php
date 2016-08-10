<?php

namespace Pgs\RestfonyBundle\Manipulator;

use Sensio\Bundle\GeneratorBundle\Manipulator\Manipulator;
use Symfony\Component\DependencyInjection\Container;

/**
 * Changes the PHP code of a YAML routing file.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class RoutingManipulator extends Manipulator
{
    private $file;

    /**
     * Constructor.
     *
     * @param string $file The YAML routing file path
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * Adds a routing resource at the top of the existing ones.
     *
     * @param string $bundle
     * @param string $prefix
     *
     * @return Boolean true if it worked, false otherwise
     *
     * @throws \RuntimeException If bundle is already imported
     */
    public function addResource($bundle, $prefix = '/')
    {
        $current = '';
        $code = sprintf(
            "%s:\n",
            Container::underscore(substr($bundle, 0, -6)) . (
            '/' !== $prefix
                ? '_' . str_replace('/', '_', substr($prefix, 1))
                : ''
            )
        );
        if (file_exists($this->file)) {
            $current = file_get_contents($this->file);

            // Don't add same bundle twice
            if (false !== strpos($current, $code)) {
                return false;
            }
        } elseif (!is_dir($dir = dirname($this->file))) {
            mkdir($dir, 0777, true);
        }

        $code .= sprintf(
            "    resource: pgs.rest.controller.%s\n    type:     rest\n",
            str_replace('/', '_', substr($prefix, 1))
        );
        $code .= "\n";
        $code .= $current;

        return file_put_contents($this->file, $code) !== false;
    }
}
