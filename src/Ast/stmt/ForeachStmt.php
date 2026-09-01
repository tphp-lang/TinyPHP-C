<?php

declare(strict_types=1);

namespace Tphp\Ast\stmt;

use Tphp\Ast\Expr;
use Tphp\Ast\Stmt;

/**
 * foreach ($arr as $k => $v)。循环变量类型由数组元素类型推出。
 * $keyVar 为空串表示无键变量。
 */
final class ForeachStmt extends Stmt
{
    public function __construct(
        public readonly Expr $arr,
        public readonly string $keyVar,
        public readonly string $valVar,
        public readonly array $body,
    ) {
        parent::__construct();
    }
}
