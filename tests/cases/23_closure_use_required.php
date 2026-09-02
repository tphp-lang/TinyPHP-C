<?php

// expect-error: 必须 use (...) 捕获

class Main
{
    public function main(): void
    {
        int $outer = 10;
        $f = function (int $v): int {
            return $v + $outer;
        };
        echo $f(1), "\n";
    }
}
