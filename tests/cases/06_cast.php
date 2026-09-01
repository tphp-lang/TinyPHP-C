<?php

// expect:
// 124
// 6.28
// 42!
// true
// false
// int(17)
// string(2) "hi"
// float(3.14)
// bool(true)
// array(3) [1, 2, 3]
// true
// 1
// 1.5
// float(1.5)
// 1.5
// c.f32(1.5)
// 3.14

class Main
{
    public function main(): void
    {
        int $n = (int)"123";
        echo $n + 1, "\n";
        double $pi = (double)"3.14";
        echo $pi * 2, "\n";
        string $t = (string)42;
        echo $t . "!", "\n";
        bool $b = (bool)"x";
        echo $b, "\n";
        bool $e = (bool)"";
        echo $e, "\n";

        int $x = 17;
        var_dump($x);
        var_dump("hi");
        var_dump($pi);
        var_dump(true);
        array<int> $arr = [1, 2, 3];
        var_dump($arr);

        echo 1 == 1 && 2 > 1, "\n";
        int $cmp = 5 > 3 ? 1 : 0;
        echo $cmp, "\n";

        // 浮点分层：float = 64 位（PHP 语义）；32 位存储用 c.f32，变量收窄需显式强转
        float $hf = 1.5;
        echo $hf, "\n";
        var_dump($hf);
        c.f32 $h32 = 1.5; // 浮点字面量可直接赋给 c.f32（编译期取值）
        echo $h32, "\n";
        var_dump($h32);
        c.f32 $h33 = (c.f32)$pi; // float 变量 → c.f32 必须显式强转
        echo $h33, "\n";
    }
}
