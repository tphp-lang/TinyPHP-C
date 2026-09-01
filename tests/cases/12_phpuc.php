<?php

// phpc：#include / #flag / #struct / c-> 直连 / c_str / php_str / null 类型 / 自动内存
// expect:
// demo_add=7
// magic=42
// area=42
// set=49
// hello from C
// cond-ok
// file-ok 42
// buf-ok
// owned-ok

#include "phpc_demo.h"
#include <stdio.h>
#include <stdlib.h>
#flag -Itests/cases
#flag tests/cases/phpc_demo.c

#struct DemoSize {
    c.i32 w;
    c.i32 h;
    c.i32 area;
}

class Main
{
    public function main(): void
    {
        // 直连函数调用：数值直传，返回 CVAL
        c->printf(c_str("demo_add=%d\n"), c->demo_add(3, 4));

        // C 常量/宏 → CVAL → 显式声明 c.i32
        c.i32 $magic = c->DEMO_MAGIC;
        echo "magic=", $magic, "\n";

        // cstruct：值语义 + 字段访问 + 按值传参
        DemoSize $s;
        $s->w = 6;
        $s->h = 7;
        c.i32 $area = c->demo_area($s);
        echo "area=", $area, "\n";
        $s->area = $area + 7;
        echo "set=", $s->area, "\n";

        // char* → string（深拷贝进字符串池）
        string $greeting = php_str(c->demo_greet());
        echo $greeting, "\n";

        // CVAL 条件（非零即真）与比较
        if (c->demo_add(1, 1) == 2) {
            echo "cond-ok\n";
        }

        // null 类型（= C void*）：句柄变量，与 null 比较，传给 C
        null $log = c->fopen(c_str("app.log"), c_str("w"));
        if ($log == null) {
            throw "cannot open app.log";
        }
        c->fprintf($log, c_str("file-ok %d\n"), 42);
        c->fclose($log);
        echo "file-ok 42\n";

        // cbuf：分配即登记，函数出口自动 free（无 free 调用）
        c.ptr $buf = cbuf(16);
        c.i32 $half = c->demo_add(4, 4);
        echo "buf-ok\n";

        // c_own：接管 C 分配的内存（同样自动 free）
        c.ptr $owned = c_own(c->malloc(32));
        if ($owned != null) {
            echo "owned-ok\n";
        }
    }
}
