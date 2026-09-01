<?php

// expect-error: 仅全局函数有效

#[export("c_main")]
class Main
{
    public function main(): void
    {
        echo "hi\n";
    }
}
