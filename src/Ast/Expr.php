<?php

declare(strict_types=1);

namespace Tphp\Ast;

use Tphp\Type\Type;

/**
 * 表达式基类。
 *
 * $type 由 Checker 回填（Type 编码），Gen 只消费不推断——
 * 这是 "checker 标注、gen 纯消费" 的边界。
 */
abstract class Expr extends Node
{
    public int $type = Type::NONE;
}
