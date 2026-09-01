<?php

// expect:
// ok=5
// v=-1
// caught: division by zero
// w=0
// propagated: division by zero
// p=-2
// res=2
// Uncaught error: division by zero

function divide(int $a, int $b): int
{
    if ($b == 0) {
        throw "division by zero";
    }
    return $a / $b;
}

// 两层调用自动传播：inner 无 or {}，错误上浮到调用者
function inner(): int
{
    return divide(1, 0);
}

function safeDiv(int $a, int $b): void
{
    int $r = divide($a, $b) or { return; };
    echo "res=", $r, "\n";
}

class Main
{
    public function main(): void
    {
        int $ok = divide(10, 2);
        echo "ok=", $ok, "\n";

        // or 块取值
        int $v = divide(1, 0) or { -1; };
        echo "v=", $v, "\n";

        // err 变量
        int $w = divide(1, 0) or {
            echo "caught: ", err, "\n";
            0;
        };
        echo "w=", $w, "\n";

        // 自动传播两层后处理
        int $p = inner() or {
            echo "propagated: ", err, "\n";
            -2;
        };
        echo "p=", $p, "\n";

        // or 内 return（void 上下文）
        safeDiv(6, 3);
        safeDiv(6, 0);

        // 顶层未捕获 → Uncaught error + 退出码 1
        divide(5, 0);
        echo "unreachable\n";
    }
}
