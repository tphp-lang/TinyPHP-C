<?php

// 多文件编译：入口 = 含 class Main 的文件；跨文件类/函数/常量免 import。
// expect:
// lib: 3 + 4 = 7
// calc: 50.24
// const: MAX=100, GREETING=hello, PI=3.14
// no-tag: ok

class Main
{
    public function main(): void
    {
        echo "lib: 3 + 4 = ", add(3, 4), "\n";
        Calculator $c = new Calculator();
        echo "calc: ", $c->area(4.0), "\n";
        echo "const: MAX=", MAX_LIMIT, ", GREETING=", GREETING, ", PI=", Calculator::PI, "\n";
        echo "no-tag: ", Helper::tag(), "\n";
    }
}
