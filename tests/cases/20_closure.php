<?php

// expect:
// 8
// 15
// 30
// 1
// 2
// 3
// 100
// hello closure
// 20
// 9

function apply(int $v, callable $f): int
{
    return $f($v);
}

function makeAdder(int $base): callable
{
    return fn (int $v): int => $v + $base;
}

function twice(int $n): int
{
    return $n * 2;
}

class Main
{
    public function main(): void
    {
        // 1. 箭头闭包：自动按值捕获
        int $step = 5;
        $add = fn (int $v): int => $v + $step;
        echo $add(3), "\n";
        echo apply(10, $add), "\n";

        // 2. function + use 按值捕获
        int $factor = 3;
        $mul = function (int $v) use ($factor): int {
            return $v * $factor;
        };
        echo $mul(10), "\n";

        // 3. 引用捕获计数器：闭包逃逸后盒子仍存活，内外共享
        int $count = 0;
        $next = function () use (&$count): int {
            $count = $count + 1;
            return $count;
        };
        echo $next(), "\n";
        echo $next(), "\n";
        echo $next(), "\n";

        // 4. 返回闭包的函数（捕获参数）+ 签名随返回值流动
        $plus100 = makeAdder(100);
        echo $plus100(0), "\n";

        // 5. 闭包体内多条语句 + 捕获变量参与拼接
        string $greeting = "hello";
        $greet = function (string $who) use ($greeting): string {
            return $greeting . " " . $who;
        };
        echo $greet("closure"), "\n";

        // 6. 引用捕获参数（跨函数共享）
        int $total = 0;
        $acc = function (int $v) use (&$total): void {
            $total = $total + $v;
        };
        $acc(12);
        $acc(8);
        echo $total, "\n";

        // 7. 管道 + 闭包调用 + 高阶组合（占位符指定管道值插入位置）
        echo 4 |> apply(..., $add), "\n";
    }
}
