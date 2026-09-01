<?php

declare(strict_types=1);

namespace Tphp\Ast\expr;

use Tphp\Ast\Expr;

/** c->符号(...) 直连 C 函数调用（参数为 C 侧值，返回 CVAL）。 */
final class CCallExpr extends Expr
{
    /** @param list<Expr> $args */
    public function __construct(
        public readonly string $name,
        public readonly array $args,
    ) {
        parent::__construct();
    }
}
