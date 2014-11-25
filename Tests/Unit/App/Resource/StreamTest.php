<?php

namespace Tests\App\Service;

use \App\Resource\Stream;


/**
 * Class StreamTest
 *
 * @package Tests\App\Service
 */
class StreamTest extends \PHPUnit_Framework_TestCase
{
    /** @var Stream */
    private $sut;

    public function tearDown()
    {
        $this->sut->close();
    }

    public function testIsBinarySetter()
    {
        $this->sut = new Stream('php://memory', 'r');
        $response = $this->sut->setIsBinary(true);
        $this->assertInstanceOf(get_class($this->sut), $response);

        $this->assertTrue($this->sut->isBinary());
    }

    public function testRead()
    {
        $string    = 'foo';
        $filename  = 'data:text/plain,' . $string;
        $this->sut = new Stream($filename, 'r');

        $response = $this->sut->rewind()->read(3);

        $this->assertEquals($string, $response);
    }

    public function testWrite()
    {
        $string    = 'foo';
        $filename  = 'php://memory';
        $this->sut = new Stream($filename, 'w');

        $response = $this->sut->write($string)->getContents();

        $this->assertEquals($string, $response);
    }

    public function testSeekAndGetChar()
    {
        $num       = 4;
        $string    = 'foo bar';
        $filename  = 'data:text/plain,' . $string;
        $this->sut = new Stream($filename, 'r');

        $response = $this->sut->seek(4)->getChar();

        $this->assertEquals($string[$num], $response);
    }

    public function testGetMeta()
    {
        $string    = 'foo bar';
        $filename  = 'data:text/plain,' . $string;
        $this->sut = new Stream($filename, 'r');

        $wrapperType = $this->sut->getMetaValue(Stream::META_WRAPPER_TYPE);
        $streamType  = $this->sut->getMetaValue(Stream::META_STREAM_TYPE);
        $seekable    = $this->sut->getMetaValue(Stream::META_SEEKABLE);


        $this->assertEquals('RFC2397', $wrapperType);
        $this->assertEquals('RFC2397', $streamType);
        $this->assertEquals('1', $seekable);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetUnknownMeta()
    {
        $string    = 'foo';
        $filename  = 'data:text/plain,' . $string;
        $this->sut = new Stream($filename, 'r');

        $this->sut->getMetaValue('unknown');
    }

    public function testGetSize()
    {
        $string    = 'foo';
        $filename  = 'data:text/plain,' . $string;
        $this->sut = new Stream($filename, 'r');

        $size = $this->sut->getSize();

        $this->assertEquals(strlen($string), $size);
    }

    public function testReadPart()
    {
        $string    = 'foobar';
        $filename  = 'data:text/plain,' . $string;
        $this->sut = new Stream($filename, 'r');

        $parts = [];
        $parts[] = $this->sut->read(3);
        $parts[] = $this->sut->read(3);
        $parts[] = $this->sut->read(3);

        $this->assertEquals('foo', $parts[0]);
        $this->assertEquals('bar', $parts[1]);
        $this->assertEquals('', $parts[2]);
    }

    public function testWriteFromStream()
    {
        $string   = 'foobar';
        $filename = 'data:text/plain,' . $string;
        $stream   = new Stream($filename, 'r');

        $this->sut = new Stream('php://memory', 'w');
        $this->sut->write($stream);

        $this->assertEquals($string, $this->sut->getContents());
        $stream->close();
    }

    public function testExistsWithFileStream()
    {
        $string    = 'foobar';
        $filename  = 'data:text/plain,' . $string;
        $this->sut = new Stream($filename, 'r');

        $this->assertTrue($this->sut->exists());
    }

    public function testNotExistsWithFileStream()
    {
        $string    = '';
        $filename  = 'data:text/plain,' . $string;
        $this->sut = new Stream($filename, 'r');

        $this->assertFalse($this->sut->exists());
    }

    public function testExistsWithStdIoStream()
    {
        $filename  = 'php://stdin';
        $this->sut = new Stream($filename, 'r');

        $this->assertFalse($this->sut->exists());
    }
}
