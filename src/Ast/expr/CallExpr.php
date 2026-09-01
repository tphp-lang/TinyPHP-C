<?php

declare(strict_types=1);

namespace Tphp\Ast\expr;

use Tphp\Ast\Expr;

/** 具名函数调用（含内置 len/dump）。 */
final class CallExpr extends Expr
{
    /** @param list<Expr> $args */
    public function __construct(
        public string $name,
        public readonly array $args,
    ) {
        parent::__construct();
    }
}
