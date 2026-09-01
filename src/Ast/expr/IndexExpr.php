<?php

declare(strict_types=1);

namespace Tphp\Ast\expr;

use Tphp\Ast\Expr;

/**
 * 下标访问。$index 为 null 表示追加写目标（$a[] = v）。
 */
final class IndexExpr extends Expr
{
    public function __construct(
        public readonly Expr $base,
        public readonly ?Expr $index,
    ) {
        parent::__construct();
    }
}
