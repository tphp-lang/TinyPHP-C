<?php

// expect-error: 未定义的变量

class Main
{
    public function main(): void
    {
        $f = fn (int $v): int => $v + $ghost;
        echo $f(1), "\n";
    }
}
