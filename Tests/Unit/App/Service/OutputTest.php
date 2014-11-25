<?php

namespace Tests\App\Service;

use \App\Service\Output;
use \App\Service\Stream;


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

    /** @var \resource */
    private $stream;

    public function setUp()
    {
        $this->sut = new Output();
        $this->sut->setStreamFactory($this->getStreamMock());

        $stream = $this->getResource();
        $resources = [
            Output::TYPE_ERR    => $stream,
            Output::TYPE_STDOUT => $stream,
            Output::TYPE_OUTPUT => $stream,
        ];

        $this->sut->setResources($resources);

        $this->getStreamMock()->expects($this->exactly(count($resources)))->method('close');
    }

    public function tearDown()
    {
        fclose($this->getResource());
    }

    /**
     * @return \resource
     */
    private function getResource()
    {
        if ($this->stream === null) {
            $this->stream = fopen('php://memory', 'w');
        }

        return $this->stream;
    }

    /**
     * @return Stream|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getStreamMock()
    {
        if ($this->streamMock === null) {
            $methods = ['close'];
            $streamMock = $this
                ->getMockBuilder('\App\Service\Stream')
                ->setMethods($methods)
                ->getMock();

            $this->streamMock = $streamMock;
        }

        return $this->streamMock;
    }

    /**
     * @return array
     */
    public function messagesDataProvider()
    {
        $class = new \stdClass();
        $class->foo = 'bar';

        return [
            ['foo', 'foo' . PHP_EOL],
            [
                ['foo' => 'bar'],
                'Array' . PHP_EOL
                . '(' . PHP_EOL
                . '    [foo] => bar' . PHP_EOL
                . ')' . PHP_EOL . PHP_EOL
            ],
            [
                $class,
                'stdClass::__set_state(array(' . PHP_EOL
                . "   'foo' => 'bar'," . PHP_EOL
                . '))' . PHP_EOL
            ],
        ];
    }

    /**
     * @param string $expected
     */
    private function assertStreamContent($expected)
    {
        $stream = $this->getResource();
        rewind($stream);
        $content = stream_get_contents($stream);

        $this->assertEquals($expected, $content);
    }

    /**
     * @dataProvider messagesDataProvider
     *
     * @param string $message
     */
    public function testErrorMessage($message, $expected)
    {
        $this->sut->error($message);
        $this->sut->__destruct();
        $this->assertStreamContent($expected);
    }

    /**
     * @dataProvider messagesDataProvider
     *
     * @param string $message
     */
    public function testOutMessage($message, $expected)
    {
        $this->sut->out($message);
        $this->sut->__destruct();
        $this->assertStreamContent($expected);
    }

    /**
     * @dataProvider messagesDataProvider
     *
     * @param string $message
     */
    public function testMessageMessage($message, $expected)
    {
        $this->sut->message($message);
        $this->sut->__destruct();
        $this->assertStreamContent($expected);
    }

}