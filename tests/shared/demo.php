<?php

// 库模式导出演示：#[export("c_add")] 把 add 以自定义 C 符号 c_add 导出，
// 未注解的 helper 仍以默认符号 tphp_helper 导出。

#[export("c_add")]
function add(int $a, int $b): int
{
    return $a + $b;
}

function helper(): int
{
    return 1;
}
