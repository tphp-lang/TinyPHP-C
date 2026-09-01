<?php

declare(strict_types=1);

namespace Tphp\Ast\expr;

use Tphp\Ast\Expr;
use Tphp\Token\TokenKind;

/**
 * 赋值表达式：= += -= *= /= %= **= .= &= |= ^= <<= >>=。
 * 目标只能是 Var / IndexExpr / PropFetch / StaticProp。
 */
final class AssignExpr extends Expr
{
    public function __construct(
        public readonly TokenKind $op,
        public readonly Expr $target,
        public readonly Expr $value,
    ) {
        parent::__construct();
    }
}
