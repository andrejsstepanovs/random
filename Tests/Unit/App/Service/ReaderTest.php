<?php

namespace Tests\App\Service;

use \App\Service\Reader;
use \App\Service\Utils;
use \App\Resource\Stream;
use \App\Value\Random;


/**
 * Class ReaderTest
 *
 * @package Tests\App\Service
 */
class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /** @var Reader */
    private $sut;

    /** @var Utils|\PHPUnit_Framework_MockObject_MockObject */
    private $utilsMock;

    /** @var Random|\PHPUnit_Framework_MockObject_MockObject */
    private $randomMock;

    /** @var Stream|\PHPUnit_Framework_MockObject_MockObject */
    private $streamMock;

    public function setUp()
    {
        $this->sut = new Reader();
        $this->sut->setUtils($this->getUtilsMock());

        $this->getRandomMock()->expects($this->any())->method('shuffle')->will($this->returnSelf());
        $this->getRandomMock()->expects($this->any())->method('slice')->will($this->returnSelf());
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
     * @return Random|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getRandomMock()
    {
        if ($this->randomMock === null) {
            $methods = [
                'getStream',
                'setChar',
                'shuffle',
                'slice',
                'count',
            ];
            $mock = $this
                ->getMockBuilder('\App\Value\Random')
                ->disableOriginalConstructor()
                ->disableProxyingToOriginalMethods()
                ->setMethods($methods)
                ->getMock();

            $this->randomMock = $mock;
        }

        return $this->randomMock;
    }

    /**
     * @return Random|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getStreamMock()
    {
        if ($this->streamMock === null) {
            $methods = [
                'setIsBinary',
                'getResource',
                'close',
                'rewind',
                'seek',
                'getChar',
                'getMetaValue',
                'isBinary',
                'getSize',
                'read',
                'write',
                'getContents',
                'exists',
                'getFirstChar',
            ];
            $mock = $this
                ->getMockBuilder('\App\Resource\Stream')
                ->disableOriginalConstructor()
                ->setMethods($methods)
                ->getMock();

            $this->streamMock = $mock;
        }

        return $this->streamMock;
    }

    public function testSetTimeoutReturnSelf()
    {
        $response = $this->sut->setTimeout(1);
        $this->assertEquals($this->sut, $response);
    }

    public function testFindRandomWithinSeekableStream()
    {
        $seekable = true;
        $type     = 'TEST';
        $count    = 2;
        $stream   = $this->getStreamMock();
        $random   = $this->getRandomMock();

        $stream->expects($this->at(0))->method('getMetaValue')->will($this->returnValue($seekable));
        $stream->expects($this->at(1))->method('getMetaValue')->will($this->returnValue($type));

        // first loop
        $stream->expects($this->at(0))->method('getChar')->will($this->returnValue('a'));
        // second loop
        $stream->expects($this->at(1))->method('getChar')->will($this->returnValue('a'));

        $streamSize = 10;
        $stream->expects($this->at(0))->method('getSize')->will($this->returnValue($streamSize));

        // before loop
        $random->expects($this->at(0))->method('count')->will($this->returnValue(0));
        // first loop
        $random->expects($this->at(1))->method('count')->will($this->returnValue(1));
        // second loop. exit
        $random->expects($this->at(2))->method('count')->will($this->returnValue(2));

        // after loop check if count match needed
        $random->expects($this->at(3))->method('count')->will($this->returnValue(2));

        // within loop
        $random->expects($this->once())->method('setChar');

        $this->sut->getRandom($stream, $count, $random);
    }

    public function testFindRandomWithinNotSeekableStream()
    {
        $seekable = false;
        $type     = 'STDIO';
        $count    = 2;
        $stream   = $this->getStreamMock();
        $random   = $this->getRandomMock();

        $this->getUtilsMock()->expects($this->once())->method('random')->will($this->returnValue(2));

        $stream->expects($this->at(0))->method('getMetaValue')->will($this->returnValue($seekable));
        $stream->expects($this->at(1))->method('getMetaValue')->will($this->returnValue($type));

        $stream->expects($this->any())->method('read')->will($this->returnValue('foo'));

        $streamSize = 10;
        $stream->expects($this->at(0))->method('getSize')->will($this->returnValue($streamSize));

        // first loop
        $random->expects($this->at(0))->method('count')->will($this->returnValue(0));
        // within loop
        $random->expects($this->at(1))->method('count')->will($this->returnValue(3));
        $random->expects($this->at(2))->method('count')->will($this->returnValue(4));

        // second loop. exit
        $random->expects($this->at(3))->method('count')->will($this->returnValue(2));

        // after loop
        $random->expects($this->at(4))->method('count')->will($this->returnValue(5));

        // after loop check if count match needed
        $random->expects($this->at(5))->method('count')->will($this->returnValue(2));
        $random->expects($this->at(7))->method('count')->will($this->returnValue(2));

        // within loop
        $random->expects($this->once())->method('setChar');

        $this->sut->getRandom($stream, $count, $random);
    }
}
