<?php

namespace App\Service;

use App\Value\Random as ValueRandom;


/**
 * Class Reader
 *
 * @package App\Service
 */
class Reader
{
    /** @var Utils */
    private $utils;

    /** @var ValueRandom */
    private $random;

    /**
     * @param ValueRandom $random
     *
     * @return $this
     */
    public function setRandom(ValueRandom $random)
    {
        $this->random = $random;

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
     * @param mixed $stream
     * @param int   $count
     *
     * @return ValueRandom
     * @throws \RuntimeException
     */
    public function getRandom($stream, $count)
    {
        $meta  = stream_get_meta_data($stream);

        if ($meta['seekable'] && $meta['stream_type'] != 'STDIO') {
            $stat = fstat($stream);
            $size = $stat['size'];

            while ($this->random->count() < $count) {
                $rand = $this->utils->random(0, $size - 1);
                fseek($stream, $rand);
                $char = fgetc($stream);
                $this->random->setChar($char);
            }
        } else {
            $finfo = finfo_open(FILEINFO_MIME);

            $binary = false;
            if ($meta['wrapper_type'] != 'http') {
                $mime   = finfo_file($finfo, $meta['uri']);
                $binary = strpos($mime, 'charset=binary') !== false;
            }

            $timeout = 2;
            $time = time();
            while ($string = fread($stream, 1024)) {
                if (time() - $time > $timeout && $this->random->count() >= $count) {
                    break;
                }

                if ($binary) {
                    $string = bin2hex($string);
                }

                if (empty($string)) {
                    throw new \RuntimeException('STDIN missing');
                }

                // we don't know if there will be more
                while ($this->random->count() < $count) {
                    $size = strlen($string);
                    $rand = $this->utils->random(0, $size - 1);
                    $char = substr($string, $rand, 1);
                    $this->random->setChar($char);
                }
            }
        }

        return $this->random->shuffle()->slice($count);
    }
}