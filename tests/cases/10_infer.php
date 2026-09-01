<?php

// expect:
// 7 1.5 true
// inferred
// 4 39
// 6 28
// point at (3,4)
// 10 10
// array<string> pin

// 类型自动推导：首次赋值定死；C 侧类型禁止推断
// expect:
// 7 1.5 true
// inferred
// 3 42
// 6 28
// point at (3,4)
// 10 10
// array<string> pin

class Point
{
    public c.i32 $x;
    public c.i32 $y;
    public function __construct(c.i32 $x, c.i32 $y)
    {
        $this->x = $x;
        $this->y = $y;
    }
    public function describe(): string
    {
        return "point at (" . (string)$this->x . "," . (string)$this->y . ")";
    }
}

function makeArray(): array<int>
{
    array<int> $a = [1, 2, 3];
    return $a;
}

class Main
{
    public function main(): void
    {
        // 标量推断：int / double / bool
        $n = 6 + 1;
        $d = 1.5;
        $b = 1 < 2;
        echo $n, " ", $d, " ", $b, "\n";

        // string 推断（含插值）
        $s = "in" . "ferred";
        echo $s, "\n";

        // 数组推断（字面量元素统合）与 len
        $arr = [1, 2, 3];
        $arr[] = 39;
        echo len($arr), " ", $arr[3], "\n";

        // 函数返回值推断
        $sq = 2 * 3;
        echo $sq, " ", $sq * 4 + 4, "\n";

        // 类实例推断
        $p = new Point(3, 4);
        echo $p->describe(), "\n";

        // 推断后类型定死：后续赋同族值合法
        $n2 = 5 + 5;
        $n3 = $n2;
        echo $n2, " ", $n3, "\n";

        // 显式声明依旧合法（混用）
        array<string> $tags = [];
        $tags[] = "array<string> pin";
        echo $tags[0], "\n";
    }
}
