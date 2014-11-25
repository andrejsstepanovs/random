<?php

namespace Tests\App\Service;

use \App\Service\Output;
use \App\Resource\Stream;
use \App\Service\StreamFactory;


/**
 * Class OutputTest
 *
 * @package Tests\App\Service
 */
class OutputTest extends \PHPUnit_Framework_TestCase
{
    /** @var Output */
    private $sut;

    /** @var Stream|\PHPUnit_Framework_MockObject_MockObject */
    private $streamMock;

    /** @var StreamFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $streamFactoryMock;

    public function setUp()
    {
        $this->sut = new Output();
        $this->sut->setStreamFactory($this->getStreamFactoryMock());

        $this->getStreamFactoryMock()
             ->expects($this->any())
             ->method('create')
             ->will($this->returnValue($this->getStreamMock()));
    }

    /**
     * @return Stream|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getStreamMock()
    {
        if ($this->streamMock === null) {
            $methods = ['close', 'write'];
            $streamMock = $this
                ->getMockBuilder('\App\Service\Stream')
                ->setMethods($methods)
                ->getMock();

            $this->streamMock = $streamMock;
        }

        return $this->streamMock;
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
     * @return array
     */
    public function messagesDataProvider()
    {
        return [
            ['foo', 'foo' . PHP_EOL],
            [1, '1' . PHP_EOL],
        ];
    }

    public function testErrorMessage()
    {
        $message = 'string';
        $this->getStreamMock()
             ->expects($this->once())
             ->method('write')
             ->with($this->equalTo($message . PHP_EOL));

        $this->sut->error($message);
    }

    public function testOutMessage()
    {
        $message = 'string';
        $this->getStreamMock()
             ->expects($this->once())
             ->method('write')
             ->with($this->equalTo($message . PHP_EOL));

        $this->sut->out($message);
    }

    public function testMessageMessage()
    {
        $message = 'string';
        $this->getStreamMock()
             ->expects($this->once())
             ->method('write')
             ->with($this->equalTo($message . PHP_EOL));

        $this->sut->message($message);
    }
}
