<?php

namespace App\Service;


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

    /** @var \resource[] */
    private $resources = [];

    /** @var Stream */
    private $stream;

    /**
     * @param Stream $stream
     *
     * @return $this
     */
    public function setStream(Stream $stream)
    {
        $this->stream = $stream;

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
     * @param string $type
     *
     * @return resource
     */
    private function getResource($type)
    {
        if (!isset($this->resources[$type])) {
            switch ($type) {
                case self::TYPE_STDOUT:
                case self::TYPE_ERR:
                case self::TYPE_OUTPUT:
                    $this->resources[$type] = $this->stream->open($type, self::DEFAULT_MODE);
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
     */
    public function error($string)
    {
        fwrite(
            $this->getResource(self::TYPE_ERR),
            $this->prepareMessage($string)
        );
    }

    /**
     * @param string $string
     */
    public function out($string)
    {
        fwrite(
            $this->getResource(self::TYPE_STDOUT),
            $this->prepareMessage($string)
        );
    }

    /**
     * @param string $string
     */
    public function message($string)
    {
        fwrite(
            $this->getResource(self::TYPE_OUTPUT),
            $this->prepareMessage($string)
        );
    }

    /**
     * @param mixed $data
     *
     * @return string
     */
    private function prepareMessage($data)
    {
        if (is_array($data)) {
            $string = print_r($data, true);
        } elseif (is_object($data)) {
            if (method_exists($data, '__toString')) {
                $string = strval($data);
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
        foreach ($this->resources as $resource) {
            $this->stream->close($resource);
        }
    }
}