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
     * @param int            $count
     *
     * @return ValueRandom
     * @throws \RuntimeException
     */
    public function getRandom(StreamResource $stream, $count)
    {
        $random = new ValueRandom();

        if ($stream->getMetaValue('seekable') && $stream->getMetaValue('stream_type') != 'STDIO') {

            $size = $stream->getSize();
            while ($random->count() < $count) {
                $rand = $this->utils->random(0, $size - 1);

                $stream->seek($rand);
                $char = $stream->getChar();
                $random->setChar($char);
            }
        } else {
            $binary  = $stream->isBinary();
            $timeout = 2;
            $time = time();
            while ($string = $stream->read()) {
                if (time() - $time > $timeout && $random->count() >= $count) {
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

        return $random->shuffle()->slice($count);
    }
}
