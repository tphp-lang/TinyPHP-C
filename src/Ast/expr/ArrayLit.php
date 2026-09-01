<?php

declare(strict_types=1);

namespace Tphp\Ast\expr;

use Tphp\Ast\Expr;

/** 数组字面量 [a, b, c]（纯列表，无键语法）。 */
final class ArrayLit extends Expr
{
    /** @param list<Expr> $items */
    public function __construct(public readonly array $items)
    {
        parent::__construct();
    }
}
