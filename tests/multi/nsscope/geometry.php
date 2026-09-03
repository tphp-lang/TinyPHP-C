<?php

namespace Geom;

const NS_LEVEL = "GEOM";

function area(double $w): string
{
    return "geometry:area";
}

function preferLocal(): string
{
    return helper(); // Geom\helper 存在：当前命名空间优先，不回退
}

function helper(): string
{
    return "ns-priority";
}

function onlyGlobal(): string
{
    return globalFallback(); // Geom 未定义 → 回退全局（PHP 函数语义）
}

function constFallback(): string
{
    return NS_LEVEL; // Geom\NS_LEVEL 命中：当前命名空间优先
}

class Rect
{
    public static function tag(): string
    {
        return "ns-class";
    }
}
