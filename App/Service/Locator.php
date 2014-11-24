<?php

namespace App\Service;


/**
 * Class Locator
 *
 * @package App\Service
 */
class Locator
{
    const COMMAND_RANDOM  = 'command.random';
    const SERVICE_OUTPUT  = 'service.output';
    const SERVICE_STREAMS = 'service.streams';
    const SERVICE_READER  = 'service.reader';
    const SERVICE_UTILS   = 'service.utils';
    const ARGUMENTS       = 'arguments';

    /** @var array */
    private $services = [];

    /**
     * @return Output
     */
    public function getServiceOutput()
    {
        if (!isset($this->services[self::SERVICE_OUTPUT])) {
            $output = new Output();
            $output->setStream($this->getServiceStream());

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
     * @return Resource
     */
    public function getServiceStream()
    {
        if (!isset($this->services[self::SERVICE_STREAMS])) {
            $this->services[self::SERVICE_STREAMS] = new Stream();
        }

        return $this->services[self::SERVICE_STREAMS];
    }

    /**
     * @return Reader
     */
    public function getServiceReader()
    {
        if (!isset($this->services[self::SERVICE_READER])) {
            $reader = new Reader();
            $reader->setUtils($this->getServiceUtils());
            $reader->setRandom(new \App\Value\Random());

            $this->services[self::SERVICE_READER] = $reader;
        }

        return $this->services[self::SERVICE_READER];
    }

    /**
     * @return Arguments
     */
    public function getArguments()
    {
        if (!isset($this->services[self::ARGUMENTS])) {
            $this->services[self::ARGUMENTS] = new Arguments();
        }

        return $this->services[self::ARGUMENTS];
    }

    /**
     * @return \App\Command\Random
     */
    public function getCommandRandom()
    {
        if (!isset($this->services[self::COMMAND_RANDOM])) {
            $command = new \App\Command\Random();
            $command->setOutput($this->getServiceOutput());
            $command->setStream($this->getServiceStream());
            $command->setUtils($this->getServiceUtils());
            $command->setReader($this->getServiceReader());

            $this->services[self::COMMAND_RANDOM] = $command;
        }

        return $this->services[self::COMMAND_RANDOM];
    }
}
