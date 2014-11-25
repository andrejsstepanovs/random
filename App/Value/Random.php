<?php

namespace App\Value;


use App\Resource\Stream as ResourceStream;
use App\Resource\Stream;
use App\Service\StreamFactory;

/**
 * Class Random
 *
 * @package App\Value
 */
class Random
{
    /** @var ResourceStream */
    private $stream;

    /** @var array */
    private $escapeChars = [
        ' ',
        PHP_EOL
    ];

    /**
     * @param ResourceStream $streamFactory
     */
    public function __construct(StreamFactory $streamFactory)
    {
        $this->streamFactory = $streamFactory;
        $this->stream        = $this->createStream();
    }

    /**
     * @return ResourceStream
     */
    private function createStream()
    {
        return $this->streamFactory->create('php://temp', 'w');
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
        $bufferStream = $this->createStream();
        $this->stream->rewind();

        while ($chars = $this->stream->read()) {
            $array = str_split($chars);
            shuffle($array);
            $bufferStream->write(implode($array));
        }

        $this->switchStreams($bufferStream);

        return $this;
    }

    /**
     * @param ResourceStream $bufferStream
     *
     * @return $this
     */
    private function switchStreams(ResourceStream $bufferStream)
    {
        $this->stream->close();
        $this->stream = $bufferStream;

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
            $bufferStream = $this->createStream();
            $this->stream->rewind();

            while ($chars = $this->stream->read()) {
                $charLen = mb_strlen($chars);

                $break      = false;
                $force      = true;
                $bufferSize = $bufferStream->getSize($force);
                if ($bufferSize + $charLen > $count) {
                    if ($count - $charLen < 0) {
                        $chars = mb_substr($chars, 0, $count - $charLen);
                    } else {
                        $chars = mb_substr($chars, 0, $count - $bufferSize);
                    }

                    $break = true;
                }

                $bufferStream->write($chars);
                if ($break) {
                    break;
                }
            }

            $this->switchStreams($bufferStream);
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
                $size += mb_strlen($chars);
            }
        }

        return $size;
    }
}
