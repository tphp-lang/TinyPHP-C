<?php

declare(strict_types=1);

namespace Tphp\Ast\expr;

use Tphp\Ast\Expr;
use Tphp\Ast\TypeRef;

/** 显式强转：(int)$x / (c.i64)$n / (string)$b ... */
final class CastExpr extends Expr
{
    public function __construct(
        public readonly TypeRef $target,
        public readonly Expr $expr,
    ) {
        parent::__construct();
    }
}
