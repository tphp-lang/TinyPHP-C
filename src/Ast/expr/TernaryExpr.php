<?php

declare(strict_types=1);

namespace Tphp\Ast\expr;

use Tphp\Ast\Expr;

final class TernaryExpr extends Expr
{
    public function __construct(
        public readonly Expr $cond,
        public readonly Expr $then,
        public readonly Expr $else,
    ) {
        parent::__construct();
    }
}
