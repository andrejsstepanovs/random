<?php

namespace App\Value;


/**
 * Class Random
 *
 * @package App\Value
 */
class Random
{
    /** @var array */
    private $chars = [];

    /**
     * @param string $char
     *
     * @return $this
     */
    public function setChar($char)
    {
        if ($this->validate($char)) {
            $this->chars[] = $char;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getChars()
    {
        return $this->chars;
    }

    /**
     * @param string $char
     *
     * @return bool
     */
    private function validate($char)
    {
        if ($char == PHP_EOL) {
            return false;
        }

        return true;
    }

    /**
     * @return $this
     */
    public function shuffle()
    {
        shuffle($this->chars);

        return $this;
    }

    /**
     * @param int $count
     *
     * @return $this
     */
    public function slice($count)
    {
        if ($this->count() < $count) {
            throw new \RuntimeException('Not enough character count');
        }

        $this->chars = array_slice($this->chars, 0, $count);

        return $this;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->chars);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return implode('', $this->chars);
    }
}
