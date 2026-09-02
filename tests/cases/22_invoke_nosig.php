<?php

// expect-error: 不可调用（需赋值闭包以推导签名）

class Main
{
    public function main(): void
    {
        callable $f = null;
        echo $f(1), "\n";
    }
}
