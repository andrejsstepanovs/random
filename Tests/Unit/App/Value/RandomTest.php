<?php

namespace Tests\App\Service;

use \App\Value\Random;
use \App\Resource\Stream;
use \App\Service\StreamFactory;
use \App\Service\Utils;


/**
 * Class RandomTest
 *
 * @package Tests\App\Service
 */
class RandomTest extends \PHPUnit_Framework_TestCase
{
    /** @var Random */
    private $sut;

    /** @var StreamFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $streamFactoryMock;

    /** @var Stream|\PHPUnit_Framework_MockObject_MockObject */
    private $streamMock;

    /** @var Utils|\PHPUnit_Framework_MockObject_MockObject */
    private $utilsMock;

    public function setUp()
    {
        $this->getStreamFactoryMock()
             ->expects($this->any())
             ->method('create')
             ->will($this->returnValue($this->getStreamMock()));

        $this->sut = new Random($this->getStreamFactoryMock(), $this->getUtilsMock());
    }

    /**
     * @return Stream|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getStreamMock()
    {
        if ($this->streamMock === null) {
            $methods = [
                'close',
                'write',
                'rewind',
                'read',
                'getMetaValue',
                'getSize'
            ];
            $streamMock = $this
                ->getMockBuilder('\App\Service\Stream')
                ->setMethods($methods)
                ->getMock();

            $this->streamMock = $streamMock;
        }

        return $this->streamMock;
    }

    /**
     * @return Utils|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getUtilsMock()
    {
        if ($this->utilsMock === null) {
            $methods = ['random'];
            $mock = $this
                ->getMockBuilder('\App\Service\Utils')
                ->setMethods($methods)
                ->getMock();

            $this->utilsMock = $mock;
        }

        return $this->utilsMock;
    }

    /**
     * @return StreamFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getStreamFactoryMock()
    {
        if ($this->streamFactoryMock === null) {
            $methods = ['create'];
            $streamMock = $this
                ->getMockBuilder('\App\Service\StreamFactory')
                ->setMethods($methods)
                ->getMock();

            $this->streamFactoryMock = $streamMock;
        }

        return $this->streamFactoryMock;
    }

    public function testGetStream()
    {
        $stream = $this->sut->getStream();

        $this->assertInstanceOf(get_class($this->getStreamMock()), $stream);
    }

    /**
     * @return array
     */
    public function validCharDataProvider()
    {
        return [
            ['a', '=', '1', '^', '!', '.']
        ];
    }

    /**
     * @dataProvider validCharDataProvider
     *
     * @param string $char
     */
    public function testSetValidChar($char)
    {
        $this->getStreamMock()
             ->expects($this->once())
             ->method('write')
             ->with($this->equalTo($char));

        $this->sut->setChar($char);
    }

    /**
     * @return array
     */
    public function notValidCharDataProvider()
    {
        return [
            [PHP_EOL, ' ']
        ];
    }

    /**
     * @dataProvider notValidCharDataProvider
     *
     * @param string $char
     */
    public function testSetNotValidChar($char)
    {
        $this->getStreamMock()
             ->expects($this->never())
             ->method('write');

        $this->sut->setChar($char);
    }

    public function testCountWithSeekable()
    {
        $size = 10;

        $this->getStreamMock()
             ->expects($this->once())
             ->method('getMetaValue')
             ->will($this->returnValue(true));

        $this->getStreamMock()
             ->expects($this->once())
             ->method('getSize')
             ->will($this->returnValue($size));

        $response = $this->sut->count();

        $this->assertEquals($size, $response);
    }

    public function testCountWithNotSeekable()
    {
        $size  = 3;
        $chars = ['foo', null];

        $this->getStreamMock()
             ->expects($this->once())
             ->method('rewind');

        $this->getStreamMock()
             ->expects($this->once())
             ->method('getMetaValue')
             ->will($this->returnValue(false));

        $this->getStreamMock()
             ->expects($this->at(2))
             ->method('read')
             ->will($this->returnValue($chars[0]));

        $this->getStreamMock()
             ->expects($this->at(3))
             ->method('read')
             ->will($this->returnValue($chars[1]));

        $response = $this->sut->count();

        $this->assertEquals($size, $response);
    }
}
