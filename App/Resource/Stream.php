<?php

namespace App\Resource;


/**
 * Class Stream
 *
 * @package App\Resource
 */
class Stream
{
    const META_WRAPPER_TYPE  = 'wrapper_type';
    const META_SEEKABLE      = 'seekable';
    const META_STREAM_TYPE   = 'stream_type';

    const STREAM_TYPE_FILE   = 'RFC2397';
    const STREAM_TYPE_SOCKET = 'tcp_socket/ssl';
    const STREAM_TYPE_STDIO  = 'STDIO';

    /** @var \resource */
    private $resource;

    /** @var array */
    private $meta;

    /** @var array */
    private $stat;

    /** @var bool */
    private $binary = false;

    /** @var string */
    private $firstChar;

    /**
     * @param \resource $resource
     */
    public function __construct($filename, $mode)
    {
        $this->resource = $this->open($filename, $mode);
    }

    /**
     * @param bool $isBinary
     *
     * @return $this
     */
    public function setIsBinary($isBinary)
    {
        $this->binary = $isBinary;

        return $this;
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

    /**
     * @return $this
     */
    public function close()
    {
        $resource = $this->getResource();
        if (is_object($resource)) {
            fclose($resource);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function rewind()
    {
        rewind($this->getResource());

        return $this;
    }

    /**
     * @param int $offset
     *
     * @return $this
     */
    public function seek($offset)
    {
        fseek($this->getResource(), $offset);

        return $this;
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
    protected function getMeta()
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
            if ($meta[self::META_WRAPPER_TYPE] != 'http') {
                $file = $meta['uri'];
                $fileInfo = finfo_open(FILEINFO_MIME);
                $mime     = finfo_file($fileInfo, $file);
                $this->binary = strpos($mime, 'charset=binary') !== false;
            }
        }

        return $this->binary;
    }

    /**
     * @param bool $force
     *
     * @return array
     */
    protected function getStat($force = false)
    {
        if ($this->stat === null || $force) {
            $this->stat = fstat($this->getResource());
        }

        return $this->stat;
    }

    /**
     * @param bool $force
     *
     * @return int
     */
    public function getSize($force = false)
    {
        $stat = $this->getStat($force);

        return $stat['size'];
    }

    /**
     * @param int $size
     *
     * @return string
     */
    public function read($size = 1024)
    {
        return fread($this->getResource(), $size);
    }

    /**
     * @param string|Stream $message
     *
     * @return $this
     */
    public function write($message)
    {
        if ($message instanceof Stream) {
            $message->rewind();
            while ($string = $message->read()) {
                $this->write($string);
            }
        } else {
            fwrite($this->getResource(), $message);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getContents()
    {
        $this->rewind();
        return stream_get_contents($this->getResource());
    }

    /**
     * @return bool
     */
    public function exists()
    {
        $stream     = $this->getResource();
        $streamType = $this->getMetaValue('stream_type');

        switch ($streamType) {
            case self::STREAM_TYPE_FILE:
                $stat = fstat($stream);
                if ($stat['size'] > 0) {
                    return true;
                }
                break;
            case self::STREAM_TYPE_SOCKET:
                $wrapperData = $this->getMetaValue('wrapper_data');
                $headers     = !empty($wrapperData) ? $wrapperData : [];
                if (!empty($headers)) {
                    foreach ($headers as $header) {
                        if (strpos($header, 'HTTP/') !== false && strpos($header, '200') !== false) {
                            return true;
                        }
                    }
                }
                break;
            case self::STREAM_TYPE_STDIO:
                stream_set_blocking($stream, false);
                $input = fread($stream, 1);
                if (!empty($input)) {
                    $this->firstChar = $input;
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
     * @return string
     */
    public function getFirstChar()
    {
        $char = $this->firstChar;

        $this->firstChar = null;

        return $char;
    }

    /**
     * Close connection
     */
    public function __destruct()
    {
        $this->close();
    }
}
