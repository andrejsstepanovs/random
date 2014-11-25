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
        $sliceCount = $count * 3;
        $binary     = $stream->isBinary();
        $time       = time();

        while ($string = $stream->read()) {
            if (time() - $time >= $this->timeout && $random->count() >= $count) {
                break;
            }

            $firstChar = $stream->getFirstChar();
            if ($firstChar) {
                $string = $firstChar . $string;
            }

            if ($binary) {
                $string = bin2hex($string);
            }

            $size = strlen($string);
            if ($size == 1) {
                continue;
            }

            // we don't know if there will be more
            $randomCount = $random->count();
            while ($randomCount < $count) {
                $rand = $this->utils->random(0, $size - 1);
                $char = substr($string, $rand, 1);

                $random->setChar($char);

                $randomCount = $random->count();
                if ($randomCount > $sliceCount) {
                    $random->shuffle()->slice($count);
                }
            }
        }

        if (!$random->count()) {
            throw new \RuntimeException('STDIN have not enough characters');
        }
    }

    /**
     * @param StreamResource $stream
     * @param int            $count
     * @param ValueRandom    $random
     *
     * @return ValueRandom
     * @throws \RuntimeException
     */
    public function getRandom(StreamResource $stream, $count, ValueRandom $random)
    {
        $seekable = $stream->getMetaValue(StreamResource::META_SEEKABLE);
        $type     = $stream->getMetaValue(StreamResource::META_STREAM_TYPE);

        if ($seekable && $type != 'STDIO') {
            $this->getRandomFromSeekableFile($stream, $random, $count);
        } else {
            $this->getRandomFromStream($stream, $random, $count);
        }

        return $random->shuffle()->slice($count);
    }
}
