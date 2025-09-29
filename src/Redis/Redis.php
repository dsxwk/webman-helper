<?php

declare(strict_types=1);

namespace Dsxwk\Framework\WebmanHelper\Redis;

use Dsxwk\Framework\Query\Handle;
use support\Redis AS Predis;

class Redis extends Predis
{
    public static function __callStatic(string $name, array $arguments)
    {
        $start    = microtime(true);
        $result   = static::connection()->{$name}(... $arguments);
        $duration = round((microtime(true) - $start) * 1000, 2);

        $redisRecord = [
            'call'     => "$name(" . json_encode($arguments) . ")",
            'duration' => $duration . ' ms',
            'result'   => $result
        ];

        Handle::setRedisRecord($redisRecord);

        return $result;
    }
}