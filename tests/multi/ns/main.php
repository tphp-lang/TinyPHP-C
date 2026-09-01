<?php

// expect:
// rect=6
// area=6
// twice=42
// P=0.001 0.001
// rect
// 0.001
// global-ok/units

// namespace：use 各形式 / FQ 直接访问 / 跨文件同 ns / 全局函数回退
// expect:
// rect=6
// area=6
// twice=42
// P=0.001 0.001
// rect
// 0.001
// global-ok/units

use Geom\{Shape, Rect as Box, function area, const PRECISION};
use function Geom\dup as twice;
use const Geom\PRECISION as G_PRECISION;

class Main
{
    public function main(): void
    {
        // use ... as：类别名
        Box $b = new Box(2.0, 3.0);
        echo $b->name(), "=", $b->area(), "\n";

        // use function
        echo "area=", area($b), "\n";

        // use function ... as
        echo "twice=", twice(21.0), "\n";

        // use const（两种别名）
        echo "P=", PRECISION, " ", G_PRECISION, "\n";

        // FQ 直接写（无需 use）：全限定类 + 常量
        Shape $s = new \Geom\Rect(1.0, 1.0);
        echo $s->name(), "\n";
        echo Geom\PRECISION, "\n";

        // FQ 静态方法（跨文件同命名空间）；Geom 内部回退调用全局函数
        echo Geom\Units::label(), "\n";
    }
}

function globalHelper(): string
{
    return "global-ok";
}
