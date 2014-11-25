<?php

namespace App\Command;

use App\Service\Utils;
use App\Service\Output;
use App\Service\StreamFactory;
use App\Service\Reader;
use App\Resource\Arguments;
use App\Resource\Stream as StreamResource;
use App\Value\Random as ValueRandom;


/**
 * Class Random
 *
 * @package App\Command
 */
class Random
{
    const TYPE_URL    = 'url';
    const TYPE_PARAM  = 'parameter';
    const TYPE_STDIN  = 'stdin';
    const TYPE_RANDOM = 'rand';

    /** @var Utils */
    private $utils;

    /** @var Output */
    private $output;

    /** @var Arguments */
    private $arguments;

    /** @var StreamFactory */
    private $streamFactory;

    /** @var Reader */
    private $reader;

    /**
     * @param Arguments $arguments
     *
     * @return $this
     */
    public function setArguments(Arguments $arguments)
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * @param Output $output
     *
     * @return $this
     */
    public function setOutput(Output $output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * @param StreamFactory $stream
     *
     * @return $this
     */
    public function setStream(StreamFactory $stream)
    {
        $this->streamFactory = $stream;

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
     * @param Reader $reader
     *
     * @return $this
     */
    public function setReader(Reader $reader)
    {
        $this->reader = $reader;

        return $this;
    }

    public function execute()
    {
        $count  = $this->arguments->getNumericArgument(1);
        $string = $this->arguments->getOtherArguments(2);

        $stream       = $this->streamFactory->create('php://temp', 'rw');
        $bufferStream = $this->streamFactory->create('php://temp', 'rw');
        $value        = new ValueRandom($stream, $bufferStream);

        $streams = $this->getStreams($string);
        $stream  = $this->selectStream($streams);
        $random  = $this->reader->getRandom($stream, $count, $value);

        $this->output->out($random->getStream());
    }

    /**
     * @param string $param
     *
     * @return StreamResource[]
     */
    private function getStreams($param)
    {
        $factory = $this->streamFactory;
        $streams = [];
        if (!empty($param)) {
            $streams[self::TYPE_URL]   = $factory->create($param);
            $streams[self::TYPE_PARAM] = $factory->create('data:text/plain,' . $param);
        }
        $streams[self::TYPE_STDIN]  = $factory->create('php://stdin');
        $streams[self::TYPE_RANDOM] = $factory->create('/dev/urandom')->setIsBinary(true);

        return array_filter(
            $streams,
            function($stream) {
                return $stream !== false;
            }
        );
    }

    /**
     * @param StreamResource[] $streams
     *
     * @return StreamResource
     * @throws \RuntimeException
     */
    private function selectStream(array $streams)
    {
        $selected = false;
        foreach ($streams as $key => $stream) {
            if (!$selected && $stream->exists()) {
                $this->output->message('selected stream: ' . $key);
                $selected = $key;
            } else {
                $stream->close();
            }
        }

        if ($selected === false) {
            throw new \RuntimeException('Failed to find valid stream');
        }

        return $streams[$selected];
    }
}
