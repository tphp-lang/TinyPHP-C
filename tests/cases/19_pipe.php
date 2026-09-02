<?php

// expect:
// 42
// 23
// hi!!
// [x]
// <y>
// int(42)
// string(15) "php-85-released"
// 8
// 10
// 5

function twice(int $n): int
{
    return $n * 2;
}

function add(int $a, int $b): int
{
    return $a + $b;
}

function exclaim(string $s): string
{
    return $s . "!";
}

function power(int $base, int $exp): int
{
    int $r = 1;
    for (int $i = 0; $i < $exp; $i = $i + 1) {
        $r = $r * $base;
    }
    return $r;
}

function trimBoth(string $s): string
{
    int $a = 0;
    int $b = len($s);
    while ($a < $b && $s[$a] == " ") {
        $a = $a + 1;
    }
    while ($b > $a && $s[$b - 1] == " ") {
        $b = $b - 1;
    }
    string $r = "";
    for (int $i = $a; $i < $b; $i = $i + 1) {
        $r = $r . $s[$i];
    }
    return $r;
}

function replace(string $s, string $from, string $to): string
{
    string $r = "";
    for (int $i = 0; $i < len($s); $i = $i + 1) {
        if ($s[$i] == $from) {
            $r = $r . $to;
        } else {
            $r = $r . $s[$i];
        }
    }
    return $r;
}

class Shout
{
    public function wrap(string $s): string
    {
        return "[" . $s . "]";
    }

    public static function tag(string $s): string
    {
        return "<" . $s . ">";
    }
}

class Main
{
    public function main(): void
    {
        echo 21 |> twice(), "\n";
        echo 20 |> add(3), "\n";
        echo "hi" |> exclaim() |> exclaim(), "\n";
        Shout $s = new Shout();
        echo "x" |> $s->wrap(), "\n";
        echo "y" |> Shout::tag(), "\n";
        42 |> var_dump();

        // 多行链式：与 PHP 8.5 的 $title |> trim(...) |> (fn($s) => ...) 同构，
        // 闭包步骤在这里写成"首参插入"的部分调用
        $title = " PHP 8.5 Released ";
        $slug = $title
            |> trimBoth()
            |> replace(" ", "-")
            |> replace(".", "")
            |> replace("P", "p")
            |> replace("H", "h")
            |> replace("R", "r");
        var_dump($slug);

        // ... 占位符：管道值插入占位符所在参数位（可任意位置，仅一次）
        echo 3 |> power(2, ...), "\n";
        echo 5 |> twice(...), "\n";
        echo 4 |> add(1, ...), "\n";
    }
}
