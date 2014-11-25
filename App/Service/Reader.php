<?php

namespace App\Service;

use App\Value\Random as ValueRandom;
use App\Resource\Stream as StreamResource;


/**
 * Class Reader
 *
 * @package App\Service
 */
class Reader
{
    /** @var Utils */
    private $utils;

    /** @var int */
    private $timeout;

    public function setTimeout($timeoutSeconds)
    {
        $this->timeout = $timeoutSeconds;

        return $this;
    }

    /**
     * @param Utils $utils
     *
     * @return $this
     */
    public function setUtils(Utils $utils)
    {
        $this->utils = $utils;

        return $this;
    }

    /**
     * @param StreamResource $stream
     * @param ValueRandom    $random
     * @param int            $count
     */
    private function getRandomFromSeekableFile(StreamResource $stream, ValueRandom $random, $count)
    {
        $size = $stream->getSize();
        while ($random->count() < $count) {
            $rand = $this->utils->random(0, $size - 1);

            $stream->seek($rand);
            $char = $stream->getChar();
            $random->setChar($char);
        }
    }

    /**
     * @param StreamResource $stream
     * @param ValueRandom    $random
     * @param int            $count
     *
     * @throws \RuntimeException
     */
    private function getRandomFromStream(StreamResource $stream, ValueRandom $random, $count)
    {
        $binary = $stream->isBinary();
        $time   = time();
        while ($string = $stream->read()) {
            if (time() - $time >= $this->timeout && $random->count() >= $count) {
                break;
            }

            if ($binary) {
                $string = bin2hex($string);
            }

            if (empty($string)) {
                throw new \RuntimeException('STDIN missing');
            }

            // we don't know if there will be more
            while ($random->count() < $count) {
                $size = strlen($string);
                $rand = $this->utils->random(0, $size - 1);
                $char = substr($string, $rand, 1);
                $random->setChar($char);
            }
        }
    }

    /**
     * @param StreamResource $stream
     * @param int            $count
     *
     * @return ValueRandom
     * @throws \RuntimeException
     */
    public function getRandom(StreamResource $stream, $count)
    {
        $random = new ValueRandom();

        if ($stream->getMetaValue('seekable') && $stream->getMetaValue('stream_type') != 'STDIO') {
            $this->getRandomFromSeekableFile($stream, $random, $count);
        } else {
            $this->getRandomFromStream($stream, $random, $count);
        }

        return $random->shuffle()->slice($count);
    }
}
