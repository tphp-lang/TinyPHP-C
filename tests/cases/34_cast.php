<?php

// 强制类型转换：机制按 C（数值截断/提升），string↔数值走 strtol/strtod 前缀解析
// expect:
// string(1) "1"
// int(1)
// int(2)
// int(-1)
// 3.14
// int(3)
// int(0)
// bool(true)
// bool(true)
// 4294967296
// bool(true)

class Main
{
    public function main(): void
    {
        string $s = (string)1;
        var_dump($s);
        int $a = (int)1.5;      // C 截断：向零取整
        var_dump($a);
        int $b = (int)2.9;
        var_dump($b);
        int $neg = (int)(0.0 - 1.9); // -1（向零）
        var_dump($neg);
        double $d = (double)"3.14";
        echo $d, "\n";
        int $i = (int)"3.9xyz"; // strtol 前缀解析 → 3
        var_dump($i);
        int $z = (int)"abc";    // 无前缀 → 0
        var_dump($z);
        bool $t1 = (bool)"x";   // 非空串即真
        var_dump($t1);
        bool $t2 = (bool)0.5;   // 非零即真
        var_dump($t2);
        c.i64 $w = (c.i64)(c.u32)4294967295;
        echo $w + 1, "\n";      // 4294967296
        bool $eq = (int)"42" == 42;
        var_dump($eq);
    }
}
