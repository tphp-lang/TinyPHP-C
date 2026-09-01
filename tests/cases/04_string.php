<?php

// expect:
// hello world, 1+2=17!
// 5
// true
// true
// e
// tinyphp
// TinyPHP 0.1
// abcde
// true
// true
// true
// true
// true

class Main
{
    public function main(): void
    {
        int $x = 17;
        string $name = "world";
        echo "hello $name, 1+2={$x}!\n";
        echo len("hello"), "\n";
        echo "abc" == "abc", "\n";
        echo "abc" < "abd", "\n";
        echo "hello"[1], "\n";
        echo "tiny" . "php", "\n";
        string $v = "TinyPHP";
        string $ver = "0.1";
        echo "$v $ver\n";
        string $acc = "";
        foreach (["a", "b", "c", "d", "e"] as $ch) {
            $acc .= $ch;
        }
        echo $acc, "\n";

        // SSO 字符串比较回归：索引/拼接产出的 SSO 串 vs 字面量/变量
        // （回归 TCC(Win64) 临时槽复用导致的比较失配，见 runtime/tphp_string.h）
        string $s = "a.b";
        echo $s[1] == ".", "\n";
        echo $s[1] != "x", "\n";
        string $cat = "ti" . "ny";
        echo $cat == "tiny", "\n";
        echo "tiny" == $cat, "\n";
        echo $cat < "tiny2", "\n";
    }
}
