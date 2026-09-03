<?php

// 常量作用域：全局常量 / 类常量 / 函数内常量（可遮蔽全局），同名遮蔽规则
// expect:
// global
// class
// class
// inner
// global
// 10 20

const LEVEL = "global";

class Config
{
    public const string LEVEL = "class";

    public static function which(): void
    {
        echo self::LEVEL, "\n";
        echo Config::LEVEL, "\n";
    }
}

function shadow(): void
{
    const LEVEL = "inner"; // 函数内常量遮蔽全局
    echo LEVEL, "\n";
}

class Main
{
    public function main(): void
    {
        echo LEVEL, "\n";
        Config::which();
        shadow();
        echo LEVEL, "\n"; // 全局不受遮蔽影响
        const MAX = 10;
        int $n = MAX * 2;
        echo MAX, " ", $n, "\n";
    }
}
