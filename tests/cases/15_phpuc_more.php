<?php

// phpc 进阶：结构体指针 / 64 位大数 / 无符号回绕 / 字符串三桥 / c_own 自动释放 /
// 平台条件 #flag（linux 才链 libm）
// expect:
// dot=48
// hypot2=2.828427
// sum=18000000000
// wrap=705032704
// joined=hello-world
// version=1.0
// chars=3
// A
// plat=win

#include "phpc_util.h"
#include <stdio.h>
#flag -Itests/cases
#flag tests/cases/phpc_util.c
#if linux
#flag -lm
#endif

#struct Point2D {
    c.i32 x;
    c.i32 y;
}

class Main
{
    public function main(): void
    {
        // C malloc 的结构体指针：c_own 登记 → 函数出口自动 free
        c.ptr $p = c_own(c->util_point_new(3, 4));
        c->util_point_scale($p, 2); // C 侧通过指针改写字段
        c->printf(c_str("dot=%d\n"), c->util_point_dot($p)); // 6*8=48

        // double 直传直返
        c->printf(c_str("hypot2=%.6f\n"), c->util_hypot2(2.0, 2.0));

        // 64 位大数：超出 TinyPHP int 范围的值由 C 侧产生与运算
        c.i64 $big = c->util_big_i64();
        c->printf(c_str("sum=%lld\n"), c->util_sum_i64($big, $big));

        // 无符号回绕（C 语义原样暴露）
        c.u32 $wrap = c->util_overflow_u32();
        c->printf(c_str("wrap=%u\n"), $wrap);

        // malloc 的 char*：类型化指针声明 + php_str 深拷贝 + c_own 自动 free
        c.char* $j = c_own(c->util_join(c_str("hello"), c_str("-world")));
        string $joined = php_str($j);
        echo "joined=", $joined, "\n";

        // 静态数据：php_str_ref 零拷贝借用
        string $ver = php_str_ref(c->util_version());
        echo "version=", $ver, "\n";

        // c.char 类型与 char 形参
        c.char $a = 97; // 'a'
        c->printf(c_str("chars=%d\n"), c->util_count_char(c_str("banana"), $a));

        c.char $up = 65; // 'A'
        c->putchar($up);
        c.i32 $nl = 10;
        c->putchar($nl);

        // 平台条件与 #flag 组合
        #if windows
        c->printf(c_str("plat=win\n"));
        #elif linux
        c->printf(c_str("plat=nix\n"));
        #endif
    }
}
