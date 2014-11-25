<?php

namespace App\Resource;

/**
 * Class Arguments
 *
 * @package App\Resource
 */
class Arguments
{
    /** @var array */
    private $arguments;

    /**
     * @param array $arguments
     *
     * @return $this
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * @return array
     */
    private function getArguments()
    {
        if ($this->arguments === null) {
            throw new \InvalidArgumentException('Arguments not provided');
        }

        return $this->arguments;
    }

    /**
     * @param int         $count
     * @param string|null $default
     *
     * @return string|null
     */
    public function getArgument($count, $default = null)
    {
        if ($default !== null && !is_string($default)) {
            throw new \InvalidArgumentException('Default value is not string');
        }

        return !empty($this->arguments[$count]) ? $this->arguments[$count] : $default;
    }

    /**
     * @return int
     */
    public function getNumericArgument($count, $default = null)
    {
        $value = $this->getArgument($count, $default);
        if (!empty($value) && is_numeric($value)) {
            return $value;
        }

        throw new \InvalidArgumentException('Provided argument "' . $value . '" is not numeric');
    }

    /**
     * @return null|string
     */
    public function getOtherArguments($startCount, $default = null)
    {
        $arguments = $this->getArguments();
        $data      = array_slice($arguments, $startCount);
        $string    = !empty($data) ? implode(' ', $data) : $default;

        return $string;
    }
}
