<?php

namespace App\Helpers;

class Timer
{
    public static function getTimeForMs(float $unixStartTime): int
    {
        return (int) round((microtime(true) - $unixStartTime) * 1000);
    }
}
