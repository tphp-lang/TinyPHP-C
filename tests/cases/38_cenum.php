<?php

// 调用 C 枚举：c-> 成员引用（枚举成员即整型常量，由 C 编译器解析）
// expect:
// 1
// 5
// true
// green=2
// 8
// 8

#include "cenum_helper.h"
#flag -Itests/cases

class Main
{
    public function main(): void
    {
        // 枚举成员引用（CVAL）→ 显式 C 类型落变量
        c.i32 $red = c->CC_RED;
        echo $red, "\n";

        // 位运算组合（CVAL 参与运算结果仍为 CVAL）
        c.i32 $mix = c->CC_RED | c->CC_BLUE;
        echo $mix, "\n";

        // 与字面量比较
        echo c->CC_BLUE == 4, "\n";

        // switch 分发（case 用 C 枚举成员）
        c.i32 $v = c->CC_GREEN;
        switch ($v) {
            case c->CC_RED:
                echo "red=", $v, "\n";
                break;
            case c->CC_GREEN:
                echo "green=", $v, "\n";
                break;
            default:
                echo "other", "\n";
        }

        // 传给 c-> 调用 + 混合宏常量运算
        int $shift = c->C_SHIFT;
        echo $mix + $shift, "\n";

        // #enum（TinyPHP 侧常量集）与 C 枚举互通
        c.i32 $sum = c->CC_BLUE + Color_BLUE;
        echo $sum, "\n";
    }
}

#enum Color {
    BLUE = 4,
}
