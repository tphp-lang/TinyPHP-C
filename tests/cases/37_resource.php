<?php

// resource 生命周期：不引入新类型——不透明句柄即 c.ptr/CVAL，
// fopen/fclose 显式配对（与 C 相同的所有权模型）
// expect:
// opened=true
// closed

#include <stdio.h>

class Main
{
    public function main(): void
    {
        null $f = c->fopen(c_str("build/tests/rsrc.txt"), c_str("w"));
        bool $ok = $f != null;
        echo "opened=", $ok, "\n";
        c->fprintf($f, c_str("line1\n"));
        c->fprintf($f, c_str("line2\n"));
        c->fclose($f);
        c->remove(c_str("build/tests/rsrc.txt"));
        echo "closed", "\n";
    }
}
