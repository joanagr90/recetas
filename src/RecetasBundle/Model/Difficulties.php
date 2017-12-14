<?php

namespace RecetasBundle\Model;

class Difficulties
{
    const UNKNOWN = 'unknown';
    const EASY = 'easy';
    const NORMAL = 'normal';
    const HARD = 'hard';

    public static function toArray()
    {
        return array(
            self::UNKNOWN => self::UNKNOWN,
            self::EASY => self::EASY,
            self::NORMAL => self::NORMAL,
            self::HARD => self::HARD
        );
    }
}
