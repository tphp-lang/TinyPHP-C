<?php

declare(strict_types=1);

namespace Tphp\Ast\expr;

use Tphp\Ast\Expr;
use Tphp\Token\TokenKind;

/** 后缀自增自减：x++ x--。 */
final class PostfixExpr extends Expr
{
    public function __construct(
        public readonly TokenKind $op,
        public readonly Expr $expr,
    ) {
        parent::__construct();
    }
}
