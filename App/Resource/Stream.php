<?php

namespace App\Resource;


/**
 * Class Stream
 *
 * @package App\Resource
 */
class Stream
{
    const TYPE = 'wrapper_type';

    /** @var \resource */
    private $resource;

    /** @var array */
    private $meta;

    /** @var array */
    private $stat;

    /** @var bool */
    private $binary;

    /**
     * @param \resource $resource
     */
    public function __construct($filename, $mode)
    {
        $this->resource = $this->open($filename, $mode);
    }

    /**
     * @param string $filename
     * @param string $mode
     *
     * @return \resource
     */
    private function open($filename, $mode)
    {
        return fopen($filename, $mode);
    }

    /**
     * @return \resource
     * @throws \RuntimeException
     */
    public function getResource()
    {
        if ($this->resource === null) {
            throw new \RuntimeException('Resource not set');
        }

        return $this->resource;
    }

    public function close()
    {
        $resource = $this->getResource();
        if (is_object($resource)) {
            fclose($resource);
        }
    }

    /**
     * @param int $offset
     *
     * @return int
     */
    public function seek($offset)
    {
        return fseek($this->getResource(), $offset);
    }

    /**
     * @return string
     */
    public function getChar()
    {
        return fgetc($this->getResource());
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        if ($this->meta === null) {
            $this->meta = stream_get_meta_data($this->getResource());
        }

        return $this->meta;
    }

    /**
     * @param string $key
     *
     * @return string|int
     * @throws \InvalidArgumentException
     */
    public function getMetaValue($key)
    {
        $meta = $this->getMeta();
        if (array_key_exists($key, $meta)) {
            return $meta[$key];
        }

        throw new \InvalidArgumentException('Meta key "' . $key . '" not found');
    }

    /**
     * @return bool
     */
    public function isBinary()
    {
        if ($this->binary === null) {
            $meta = $this->getMeta();
            $this->binary = false;
            if ($meta[self::TYPE] != 'http') {
                $fileInfo = finfo_open(FILEINFO_MIME);
                $mime     = finfo_file($fileInfo, $meta['uri']);
                $this->binary = strpos($mime, 'charset=binary') !== false;
            }
        }

        return $this->binary;
    }

    /**
     * @return array
     */
    public function getStat()
    {
        if ($this->stat === null) {
            $this->stat = fstat($this->getResource());
        }

        return $this->stat;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        $stat = $this->getStat();

        return $stat['size'];
    }

    /**
     * @return string
     */
    public function read()
    {
        return fread($this->getResource(), 1024);
    }

    /**
     * @param string $message
     *
     * @return int
     */
    public function write($message)
    {
        $bytesWritten = fwrite($this->getResource(), $message);

        return $bytesWritten > 0;
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * @return bool
     */
    public function exists()
    {
        $stream = $this->getResource();
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
}
