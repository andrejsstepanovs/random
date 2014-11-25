<?php

namespace Tests\App\Service;

use \App\Resource\Arguments;


/**
 * Class ArgumentsTest
 *
 * @package Tests\App\Service
 */
class ArgumentsTest extends \PHPUnit_Framework_TestCase
{
    /** @var Arguments */
    private $sut;

    public function setUp()
    {
        $this->sut = new Arguments();
    }

    public function argumentsDataProvider()
    {
        return [
            [
                ['foo', 'bar'],
                0,
                null,
                'foo'
            ],
            [
                ['foo', 'bar'],
                1,
                null,
                'bar'
            ],
            [
                ['foo', 'bar'],
                2,
                'apple',
                'apple'
            ],
        ];
    }

    /**
     * @dataProvider argumentsDataProvider
     *
     * @param array  $arguments
     * @param int    $count
     * @param string $default
     * @param string $expected
     */
    public function testGetArgument(array $arguments, $count, $default, $expected)
    {
        $response = $this->sut->setArguments($arguments)->getArgument($count, $default);

        $this->assertEquals($expected, $response);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDefaultValueNotString()
    {
        $this->sut->getArgument(1, []);
    }

    public function testGetNumericArgument()
    {
        $arguments = ['foo', '123', 'bar'];
        $response = $this->sut->setArguments($arguments)->getNumericArgument(1);

        $this->assertEquals($response, 123);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetNumericArgumentNotNumeric()
    {
        $arguments = ['foo', '123', 'bar'];
        $this->sut->setArguments($arguments)->getNumericArgument(2);
    }

    public function testGetOtherArguments()
    {
        $arguments = ['foo', '123', 'apple banana'];
        $response = $this->sut->setArguments($arguments)->getOtherArguments(1);

        $expected = '123 apple banana';
        $this->assertEquals($expected, $response);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testArgumentsNotProvided()
    {
        $this->sut->getOtherArguments(1);
    }
}
