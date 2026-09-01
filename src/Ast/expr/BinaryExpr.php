<?php

declare(strict_types=1);

namespace Tphp\Ast\expr;

use Tphp\Ast\Expr;
use Tphp\Token\TokenKind;

final class BinaryExpr extends Expr
{
    public function __construct(
        public readonly TokenKind $op,
        public readonly Expr $left,
        public readonly Expr $right,
    ) {
        parent::__construct();
    }
}
