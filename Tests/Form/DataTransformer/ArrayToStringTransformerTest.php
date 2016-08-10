<?php

namespace Form\DataTransformer;

use Pgs\RestfonyBundle\Form\DataTransformer\ArrayToStringTransformer;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ArrayToStringTransformerTest extends TestCase
{
    /**
     * @var ArrayToStringTransformer
     */
    private $transformer;

    public function setup()
    {
        $this->transformer = new ArrayToStringTransformer();
    }

    /**
     * @test
     */
    public function itShouldThrowExceptionIfArgumentIsNotArrayWhenTransformMethodIsInvoked()
    {
        $this->expectException(TransformationFailedException::class);

        $this->transformer->transform('test');
    }

    /**
     * @test
     * @dataProvider initialArrayProvider
     * @param array $initialArray
     * @param array $expectedArray
     */
    public function returnArrayIfArgumentIsStringWhenReverseTransformMethodIsInvoked($initialArray, $expectedArray)
    {
        $string = implode(',', $initialArray);
        $resultArray = $this->transformer->reverseTransform($string);

        $this->assertSame($expectedArray, $resultArray);
    }

    public function initialArrayProvider()
    {
        return [
            [['1', 'a b ', '3'], ['1', 'a b', '3']],
            [['Ą', 'a', '3'], ['Ą', 'a', '3']]
        ];
    }

    /**
     * @test
     * @dataProvider initialStringProvider
     * @param $initialString
     */
    public function itShouldReturnStringIfArrayIsPassedToTransformMethod($initialString)
    {
        $array = explode(',', $initialString);
        $resultString = $this->transformer->transform($array);

        $this->assertSame($initialString, $resultString);
    }

    public function initialStringProvider()
    {
        return [
            ['1,2,3,4 ,5'],
            ['   4   ą,ą , ą,2 ']
        ];
    }

    /**
     * @test
     */
    public function itShouldReturnEmptyArrayIfEmptyStringIsPassedToReverseTransformMethod()
    {
        $string = '';
        $resultArray = $this->transformer->reverseTransform($string);

        $this->assertEmpty($resultArray);
    }

    /**
     * @test
     * @dataProvider initialArrayProvider
     */
    public function itShouldReturnArrayIfStringIsArray($initialArray)
    {
        $resultArray = $this->transformer->reverseTransform($initialArray);

        $this->assertSame($initialArray, $resultArray);
    }
}
