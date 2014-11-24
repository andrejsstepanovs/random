<?php

namespace App\Service;


/**
 * Class Utils
 *
 * @package App\Service
 */
class Utils
{
    /**
     * @param int $min
     * @param int $max
     *
     * @return int
     */
    public function random($min, $max)
    {
        if ($min >= $max) {
            throw new \InvalidArgumentException('Random min >= max');
        }

        return mt_rand($min, $max);
    }
}