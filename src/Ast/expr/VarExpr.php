<?php

declare(strict_types=1);

namespace Tphp\Ast\expr;

use Tphp\Ast\Expr;

/** 变量引用（$name）。sym 由 Checker 回填（盒子变量的读写文本生成用）。 */
final class VarExpr extends Expr
{
    public ?object $sym = null;

    public function __construct(public readonly string $name)
    {
        parent::__construct();
    }
}
