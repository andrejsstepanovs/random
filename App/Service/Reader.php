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

    /**
     * @param int $timeoutSeconds
     *
     * @return $this
     */
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

            $size = mb_strlen($string);
            if ($size == 1) {
                continue;
            }

            // we don't know if there will be more
            $wordPopulationTime = time();
            do {
                $rand = $this->utils->random(0, $size - 1);
                $char = mb_substr($string, $rand, 1);

                $random->setChar($char);

                $randomCount = $random->count();
                if ($randomCount >= $sliceCount) {
                    $random->shuffle()->slice($count);
                }
            } while ($randomCount < $count && time() - $wordPopulationTime < $this->timeout);
        }

        if (!$random->count()) {
            throw new \RuntimeException('STDIN have not enough characters');
        }

        $random->shuffle()->slice($count);
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

        $randomCount = $random->count();
        if ($randomCount != $count) {
            $msg = 'Failed to find correct random character count. (' . $randomCount . ')';
            throw new \RuntimeException($msg);
        }

        return $random->shuffle()->slice($count);
    }
}
