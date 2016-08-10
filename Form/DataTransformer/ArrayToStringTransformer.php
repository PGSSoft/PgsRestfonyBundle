<?php

namespace Pgs\RestfonyBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class transforms norm data into view data.
 */
class ArrayToStringTransformer implements DataTransformerInterface
{
    /**
     * Transforms an array to a csv set of strings.
     *
     * @param array|null $array
     *
     * @return string
     */
    public function transform($array)
    {
        if (!is_array($array)) {
            throw new TransformationFailedException(sprintf(
                '%s is not an array',
                gettype($array)
            ));
        }

        return implode(',', $array);
    }

    /**
     * Transforms a string into an array.
     *
     * @param string $string
     *
     * @return array
     */
    public function reverseTransform($string)
    {
        //JSON requests will already be strings
        if (is_array($string)) {
            return $string;
        }
        //strip any whitespace
        $string = preg_replace('/^\s+|\s+$/u', '', $string);
        $string = preg_replace('/\s+,\s*/u', ',', $string);
        if (empty($string)) {
            return [];
        }

        return explode(',', $string);
    }
}
