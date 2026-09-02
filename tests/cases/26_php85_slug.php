<?php

// PHP 8.5 管道示例的 TinyPHP 版（doc/grammar.md 管道 + 一等可调用 + 闭包）：
//   $title |> trim(...) |> (fn($s) => str_replace(...)) |> strtolower(...)
// （语言无 ord/chr 内置，小写化以逐字母替换表达——示例重点在管道形态）
// expect:
// string(15) "php-85-released"

function trim_s(string $s): string
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

function str_replace_s(string $s, string $from, string $to): string
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

class Main
{
    public function main(): void
    {
        $title = " PHP 8.5 Released ";
        $lower = $title
            |> trim_s(...)
            |> (fn (string $str) => str_replace_s($str, "P", "p"))
            |> (fn (string $str) => str_replace_s($str, "H", "h"))
            |> (fn (string $str) => str_replace_s($str, "R", "r"));
        // 与 PHP 8.5 示例同构的最终管道：trim → 空格转横线 → 去点号
        $slug = $lower
            |> (fn (string $str) => str_replace_s($str, " ", "-"))
            |> (fn (string $str) => str_replace_s($str, ".", ""));
        var_dump($slug);
    }
}
