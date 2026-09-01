<?php

declare(strict_types=1);

namespace Tphp\Ast\expr;

use Tphp\Ast\Expr;

/**
 * 裸名字：常量引用（MAX_LIMIT / 函数内 const）或 self::/parent:: 的前半段。
 * Checker 解析后回填 isLocal（函数内常量）与 type。
 */
final class NameExpr extends Expr
{
    public bool $isLocal = false;

    public function __construct(public string $name)
    {
        parent::__construct();
    }
}
