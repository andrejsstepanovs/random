<?php

namespace App\Service;

use App\Command\Random;


/**
 * Class Locator
 *
 * @package App\Service
 */
class Locator
{
    const COMMAND_RANDOM = 'command.random';
    const SERVICE_OUTPUT = 'service.output';
    const STREAM_FACTORY = 'service.streams';
    const SERVICE_READER = 'service.reader';
    const SERVICE_UTILS  = 'service.utils';

    /** @var array */
    private $services = [];

    /** @var array */
    private $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $key
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    private function getConfigValue($key)
    {
        if (array_key_exists($key, $this->config)) {
            return $this->config[$key];
        }

        throw new \InvalidArgumentException('Config "' . $key . ' not provided');
    }

    /**
     * @return Output
     */
    public function getServiceOutput()
    {
        if (!isset($this->services[self::SERVICE_OUTPUT])) {
            $output = new Output();
            $output->setStreamFactory($this->getStreamFactory());

            $this->services[self::SERVICE_OUTPUT] = $output;
        }

        return $this->services[self::SERVICE_OUTPUT];
    }

    /**
     * @return Utils
     */
    public function getServiceUtils()
    {
        if (!isset($this->services[self::SERVICE_UTILS])) {
            $this->services[self::SERVICE_UTILS] = new Utils();
        }

        return $this->services[self::SERVICE_UTILS];
    }

    /**
     * @return StreamFactory
     */
    public function getStreamFactory()
    {
        if (!isset($this->services[self::STREAM_FACTORY])) {
            $this->services[self::STREAM_FACTORY] = new StreamFactory();
        }

        return $this->services[self::STREAM_FACTORY];
    }

    /**
     * @return Reader
     */
    public function getServiceReader()
    {
        if (!isset($this->services[self::SERVICE_READER])) {
            $reader = new Reader();
            $reader->setUtils($this->getServiceUtils());
            $reader->setTimeout($this->getConfigValue('timeout'));

            $this->services[self::SERVICE_READER] = $reader;
        }

        return $this->services[self::SERVICE_READER];
    }

    /**
     * @return \App\Command\Random
     */
    public function getCommandRandom()
    {
        if (!isset($this->services[self::COMMAND_RANDOM])) {
            $command = new Random();
            $command->setOutput($this->getServiceOutput());
            $command->setStreamFactory($this->getStreamFactory());
            $command->setReader($this->getServiceReader());

            $this->services[self::COMMAND_RANDOM] = $command;
        }

        return $this->services[self::COMMAND_RANDOM];
    }
}
