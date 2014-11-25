<?php

namespace App\Service;

use App\Resource\Stream as StreamResource;


/**
 * Class Output
 *
 * @package App\Service
 */
class Output
{
    const TYPE_ERR     = 'php://stderr';
    const TYPE_STDOUT  = 'php://stdout';
    const TYPE_OUTPUT  = 'php://output';
    const DEFAULT_MODE = 'w';

    /** @var Resource[] */
    private $resources = [];

    /** @var StreamFactory */
    private $streamFactory;

    /**
     * @param StreamFactory $stream
     *
     * @return $this
     */
    public function setStreamFactory(StreamFactory $stream)
    {
        $this->streamFactory = $stream;

        return $this;
    }

    /**
     * @param array $resources
     *
     * @return $this
     */
    public function setResources(array $resources)
    {
        $this->resources = $resources;

        return $this;
    }

    /**
     * @param $type
     *
     * @return StreamResource
     * @throws \InvalidArgumentException
     */
    private function getResource($type)
    {
        if (!isset($this->resources[$type])) {
            switch ($type) {
                case self::TYPE_STDOUT:
                case self::TYPE_ERR:
                case self::TYPE_OUTPUT:
                    $this->resources[$type] = $this->streamFactory->create($type, self::DEFAULT_MODE);
                    break;
                default:
                    throw new \InvalidArgumentException('Resource type "' . $type . '" unknown');
                    break;
            }
        }

        return $this->resources[$type];
    }

    /**
     * @param string $string
     * @param bool   $newline
     */
    public function error($string, $newline = true)
    {
        $this->write(self::TYPE_ERR, $string, $newline);
    }

    /**
     * @param string $string
     * @param bool   $newline
     */
    public function out($string, $newline = true)
    {
        $this->write(self::TYPE_STDOUT, $string, $newline);
    }

    /**
     * @param string $string
     * @param bool   $newline
     */
    public function message($string, $newline = true)
    {
        $this->write(self::TYPE_OUTPUT, $string, $newline);
    }

    /**
     * @param string $type
     * @param mixed  $string
     * @param bool   $newLine
     */
    private function write($type, $string, $newLine = true)
    {
        $this->getResource($type)->write($this->prepareMessage($string, $newLine));
    }

    /**
     * @param mixed $data
     *
     * @return string|StreamResource
     */
    private function prepareMessage($data)
    {
        if (is_array($data)) {
            $string = print_r($data, true);
        } elseif (is_object($data)) {
            if (method_exists($data, '__toString')) {
                $string = strval($data);
            } elseif ($data instanceof StreamResource) {
                return $data;
            } else {
                $string = var_export($data, true);
            }
        } else {
            $string = $data;
        }

        return $string . PHP_EOL;
    }

    public function __destruct()
    {
        /** @var StreamResource $resource */
        foreach ($this->resources as $resource) {
            $resource->close();
        }
    }
}
