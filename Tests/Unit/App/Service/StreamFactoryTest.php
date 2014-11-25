<?php

namespace Tests\App\Service;

use \App\Service\StreamFactory;


/**
 * Class StreamFactoryTest
 *
 * @package Tests\App\Service
 */
class StreamFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var StreamFactory */
    private $sut;

    public function setUp()
    {
        $this->sut = new StreamFactory();
    }

    public function testValidStreamCreate()
    {
        $filename = 'php://memory';
        $mode     = 'r';
        $response = $this->sut->create($filename, $mode);

        $this->assertInstanceOf('\App\Resource\Stream', $response);
    }
}
