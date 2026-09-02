<?php

// c_fn：闭包 → C 回调函数指针（约定：C 回调最后一个参数为 void* userdata）
// expect:
// cfn=18

#include "cfn_helper.h"
#flag -Itests/cases
#flag tests/cases/cfn_helper.c

class Main
{
    public function main(): void
    {
        int $bias = 3;
        $cb = fn (int $v): int => $v + $bias;
        // c_fn 返回 C 函数指针（尾参 ud 形参保留但被 trampoline 忽略）
        c.ptr $f = c_fn($cb);
        // C 侧：cfn_apply(5, cb, ud) → cb(5, ud)+10 → (5+3)+10 = 18
        int $r = c->cfn_apply(5, $f, null);
        echo "cfn=", $r, "\n";
    }
}
