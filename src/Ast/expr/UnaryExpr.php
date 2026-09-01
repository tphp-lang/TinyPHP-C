<?php

declare(strict_types=1);

namespace Tphp\Ast\expr;

use Tphp\Ast\Expr;
use Tphp\Token\TokenKind;

/** 前缀一元：-x +x !x ~x ++x --x。 */
final class UnaryExpr extends Expr
{
    public function __construct(
        public readonly TokenKind $op,
        public readonly Expr $expr,
    ) {
        parent::__construct();
    }
}
