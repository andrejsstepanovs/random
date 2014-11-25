<?php

namespace App\Service;

use App\Resource\Stream as StreamResource;


/**
 * Class StreamFactory
 *
 * @package App\Service
 */
class StreamFactory
{
    const DEFAULT_MODE = 'r';

    /**
     * @param string $filename
     * @param string $mode
     *
     * @return StreamResource|bool
     */
    public function create($filename, $mode = self::DEFAULT_MODE)
    {
        $stream = false;
        if (!empty($filename)) {
            set_error_handler([get_class(), 'errorHandler']);
            try {
                $stream = new StreamResource($filename, $mode);
            } catch (\RuntimeException $exc) {
            }
            restore_error_handler();
        }

        return $stream;
    }

    /**
     * Throwing php error message as exception
     *
     * @param int    $number
     * @param string $string
     * @param string $file
     * @param int    $line
     * @param array  $context
     *
     * @throws \RuntimeException
     */
    public static function errorHandler($number, $string, $file, $line, array $context)
    {
        throw new \RuntimeException($string, $number);
    }
}
