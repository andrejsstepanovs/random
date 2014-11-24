<?php

namespace App\Service;


/**
 * Class Stream
 *
 * @package App\Service
 */
class Stream
{
    const DEFAULT_MODE = 'r';

    /**
     * @param array $streams
     *
     * @return \resource[]
     */
    public function filterValidStreams(array $streams)
    {
        return array_filter(
            $streams,
            function($stream) {
                return $stream !== false;
            }
        );
    }

    /**
     * @param string $filename
     * @param string $mode
     *
     * @return \resource|bool
     */
    public function open($filename, $mode = self::DEFAULT_MODE)
    {
        $stream = false;
        if (!empty($filename)) {
            set_error_handler([get_class(), 'errorHandler']);
            try {
                $stream = fopen($filename, $mode);
            } catch (\RuntimeException $exc) {
            }
            restore_error_handler();
        }

        return $stream;
    }

    /**
     * @param \resource $stream
     *
     * @return $this
     */
    public function close($stream)
    {
        $success = fclose($stream);
        if (!$success) {
            throw new \RuntimeException('Failed to close resource');
        }

        return $this;
    }

    /**
     * @param \resource $stream
     *
     * @return string
     */
    public function exists($stream)
    {
        $meta = stream_get_meta_data($stream);

        switch ($meta['stream_type']) {
            case 'RFC2397': // file
                $stat = fstat($stream);
                if ($stat['size'] > 0) {
                    return true;
                }
                break;
            case 'tcp_socket/ssl': // url
                $headers = !empty($meta['wrapper_data']) ? $meta['wrapper_data'] : [];
                if (!empty($headers)) {
                    foreach ($headers as $header) {
                        if (strpos($header, 'HTTP/1.0') !== false && strpos($header, '200') !== false) {
                            return true;
                        }
                    }
                }
                break;
            case 'STDIO': // stdin
                stream_set_blocking($stream, false);
                $input = fread($stream, 1);
                if (!empty($input)) {
                    return true;
                }
                break;
            default:
                throw new \RuntimeException('Unknown stream');
                break;
        }

        return false;
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