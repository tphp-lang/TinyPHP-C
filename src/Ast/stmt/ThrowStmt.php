<?php

declare(strict_types=1);

namespace Tphp\Ast\stmt;

use Tphp\Ast\Expr;
use Tphp\Ast\Stmt;

/** throw 语句：抛出字符串错误消息（自动沿调用链上浮，直到最近的 or {}）。 */
final class ThrowStmt extends Stmt
{
    public function __construct(public readonly Expr $expr)
    {
        parent::__construct();
    }
}
