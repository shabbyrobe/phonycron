<?php
namespace Phonycron;

final class Functions
{
    private function __construct() {}

    static function ensureDateTime($date)
    {
        if (is_numeric($date)) {
            if (static::$tz) {
                $date = new \DateTime('@'.$date, static::$tz);
            } else {
                $date = new \DateTime('@'.$date);
            }
        }
        if (!$date instanceof \DateTime) {
            throw new \InvalidArgumentException();
        }
        return $date;
    }
}
