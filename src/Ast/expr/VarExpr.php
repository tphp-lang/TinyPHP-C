<?php

declare(strict_types=1);

namespace Tphp\Ast\expr;

use Tphp\Ast\Expr;

/** 变量引用（$name）。 */
final class VarExpr extends Expr
{
    public function __construct(public readonly string $name)
    {
        parent::__construct();
    }
}
