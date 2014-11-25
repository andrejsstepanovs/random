<?php

namespace Tests\App\Service;

use \App\Service\Utils;


/**
 * Class UtilsTest
 *
 * @package Tests\App\Service
 */
class UtilsTest extends \PHPUnit_Framework_TestCase
{
    /** @var Utils */
    private $sut;

    public function setUp()
    {
        $this->sut = new Utils();
    }

    /**
     * @return array
     */
    public function validDataProvider()
    {
        return [
            [1, 2],
            [0, 1],
            [1, 99],
        ];
    }

    /**
     * @dataProvider validDataProvider
     *
     * @param int $min
     * @param int $max
     */
    public function testGenerateRandom($min, $max)
    {
        $response = $this->sut->random($min, $max);

        $this->assertGreaterThanOrEqual($min, $response);
        $this->assertLessThanOrEqual($max, $response);
    }

    /**
     * @return array
     */
    public function notValidDataProvider()
    {
        return [
            [2, 1],
            [1, 0],
            [99, 1],
        ];
    }

    /**
     * @dataProvider notValidDataProvider
     *
     * @expectedException \InvalidArgumentException
     *
     * @param int $min
     * @param int $max
     */
    public function testInvalidArguments($min, $max)
    {
        $this->sut->random($min, $max);
    }
}
