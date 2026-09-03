<?php

// 命名空间作用域（多文件）：裸名解析优先当前命名空间；
// 函数在当前命名空间未命中时回退全局（PHP 语义）；常量同类；类不回退
// expect:
// geometry:area
// units:meter
// ns-priority
// global fallback
// GEOM
// global fallback
// ns-class

use function Geom\{area, preferLocal, onlyGlobal, constFallback};
use function Units\unit;
use Geom\Rect;

class Main
{
    public function main(): void
    {
        echo area(2.0), "\n";       // Geom\area（use 导入）
        echo unit(), "\n";          // Units\unit
        echo preferLocal(), "\n";   // 当前 ns 命中 → 不回退
        echo onlyGlobal(), "\n";    // Geom 未定义 → 回退全局
        echo constFallback(), "\n"; // 常量同样回退
        echo helper(), "\n";        // 全局 helper
        echo Rect::tag(), "\n";
    }
}
