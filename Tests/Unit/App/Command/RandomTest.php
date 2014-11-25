<?php

namespace Tests\App\Command;

use \App\Command\Random;
use \App\Resource\Stream;
use \App\Service\StreamFactory;
use \App\Service\Reader;
use \App\Resource\Arguments;
use \App\Service\Output;
use \App\Value\Random as ValueRandom;


/**
 * Class RandomTest
 *
 * @package Tests\App\Service
 */
class RandomTest extends \PHPUnit_Framework_TestCase
{
    /** @var Random */
    private $sut;

    /** @var Stream|\PHPUnit_Framework_MockObject_MockObject */
    private $streamMock;

    /** @var StreamFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $streamFactoryMock;

    /** @var Arguments|\PHPUnit_Framework_MockObject_MockObject */
    private $argumentsMock;

    /** @var Reader|\PHPUnit_Framework_MockObject_MockObject */
    private $readerMock;

    /** @var Output|\PHPUnit_Framework_MockObject_MockObject */
    private $outputMock;

    /** @var ValueRandom|\PHPUnit_Framework_MockObject_MockObject */
    private $valueRandomMock;

    public function setUp()
    {
        $this->sut = new Random();
        $this->sut->setArguments($this->getArgumentsMock());
        $this->sut->setReader($this->getReaderMock());
        $this->sut->setOutput($this->getOutputMock());
        $this->sut->setStreamFactory($this->getStreamFactoryMock());
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

    /**
     * @return Output|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getOutputMock()
    {
        if ($this->outputMock === null) {
            $methods = ['out', 'message'];
            $streamMock = $this
                ->getMockBuilder('\App\Service\Output')
                ->setMethods($methods)
                ->getMock();

            $this->outputMock = $streamMock;
        }

        return $this->outputMock;
    }

    /**
     * @return Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getReaderMock()
    {
        if ($this->readerMock === null) {
            $methods = ['getRandom'];
            $mock = $this
                ->getMockBuilder('\App\Service\Reader')
                ->setMethods($methods)
                ->getMock();

            $this->readerMock = $mock;
        }

        return $this->readerMock;
    }

    /**
     * @return Arguments|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getArgumentsMock()
    {
        if ($this->argumentsMock === null) {
            $methods = ['getNumericArgument', 'getOtherArguments'];
            $streamMock = $this
                ->getMockBuilder('\App\Resource\Arguments')
                ->setMethods($methods)
                ->getMock();

            $this->argumentsMock = $streamMock;
        }

        return $this->argumentsMock;
    }

    /**
     * @return Random|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getStreamMock()
    {
        if ($this->streamMock === null) {
            $methods = [
                'setIsBinary',
                'exists',
                'close'
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

    /**
     * @return ValueRandom|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getValueRandomMock()
    {
        if ($this->valueRandomMock === null) {
            $methods = [
                'getStream',
            ];
            $mock = $this
                ->getMockBuilder('\App\Value\Random')
                ->disableOriginalConstructor()
                ->setMethods($methods)
                ->getMock();

            $this->valueRandomMock = $mock;
        }

        return $this->valueRandomMock;
    }

    public function testExecute()
    {
        $this->getStreamMock()
             ->expects($this->any())
             ->method('setIsBinary')
             ->will($this->returnSelf());

        $this->getReaderMock()
             ->expects($this->any())
             ->method('getRandom')
             ->will($this->returnValue($this->getValueRandomMock()));

        $this->getStreamFactoryMock()
             ->expects($this->any())
             ->method('create')
             ->will($this->returnValue($this->getStreamMock()));

        $this->getStreamMock()
             ->expects($this->any())
             ->method('exists')
             ->will($this->returnValue(true));

        $response = $this->sut->execute();

        $this->assertInstanceOf(get_class($this->sut), $response);
    }
}