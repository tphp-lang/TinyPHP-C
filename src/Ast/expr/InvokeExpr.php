<?php

declare(strict_types=1);

namespace Tphp\Ast\expr;

use Tphp\Ast\Expr;

/** 闭包调用：$f(args)。callee 必须是 VarExpr（callable 变量）。sig 由 Checker 回填。 */
final class InvokeExpr extends Expr
{
    /** @var array{ret: int, params: list<int>}|null */
    public ?array $sig = null;

    /** @param list<Expr> $args */
    public function __construct(
        public readonly Expr $callee,
        public readonly array $args,
    ) {
        parent::__construct();
    }
}
