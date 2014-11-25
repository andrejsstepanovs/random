<?php

namespace App\Value;


use App\Resource\Stream as ResourceStream;
use App\Resource\Stream;

/**
 * Class Random
 *
 * @package App\Value
 */
class Random
{
    /** @var ResourceStream */
    private $stream;

    /** @var ResourceStream */
    private $bufferStream;

    /** @var array */
    private $escapeChars = [
        ' ',
        PHP_EOL
    ];

    /**
     * @param ResourceStream $stream
     */
    public function __construct(ResourceStream $stream, ResourceStream $bufferStream)
    {
        $this->stream       = $stream;
        $this->bufferStream = $bufferStream;
    }

    /**
     * @return ResourceStream
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * @param string $char
     *
     * @return $this
     */
    public function setChar($char)
    {
        if ($this->validate($char)) {
            $this->stream->write($char);
        }

        return $this;
    }

    /**
     * @param string $char
     *
     * @return bool
     */
    private function validate($char)
    {
        if (in_array($char, $this->escapeChars, true)) {
            return false;
        }

        return true;
    }

    /**
     * @return $this
     */
    public function shuffle()
    {
        $this->bufferStream->rewind();
        $this->stream->rewind();

        while ($chars = $this->stream->read()) {
            $array = str_split($chars);
            shuffle($array);
            $this->bufferStream->write(implode($array));
        }

        $this->switchStreams();

        return $this;
    }

    /**
     * @return $this
     */
    private function switchStreams()
    {
        $stream       = $this->stream;
        $bufferStream = $this->bufferStream;

        $this->stream       = $bufferStream;
        $this->bufferStream = $stream;

        return $this;
    }

    /**
     * @param int $count
     *
     * @return $this
     */
    public function slice($count)
    {
        if ($this->count() > $count) {
            $this->bufferStream->rewind();
            $this->stream->rewind();

            $length = 0;
            while ($chars = $this->stream->read()) {
                $length += strlen($chars);

                if ($length > $count) {
                    break;
                }

                $this->bufferStream->write($chars);
            }

            $this->switchStreams();
        }

        return $this;
    }

    /**
     * @return int
     */
    public function count()
    {
        if ($this->stream->getMetaValue(Stream::META_SEEKABLE)) {
            $force = true;
            $size  = $this->stream->getSize($force);
        } else {
            $size = 0;
            $this->stream->rewind();
            while ($chars = $this->stream->read()) {
                $size += strlen($chars);
            }
        }

        return $size;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->stream->getContents();
    }
}
