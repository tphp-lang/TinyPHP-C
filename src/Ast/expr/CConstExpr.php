<?php

declare(strict_types=1);

namespace Tphp\Ast\expr;

use Tphp\Ast\Expr;

/** c->符号 C 常量/宏引用（原样输出 C 表达式，类型 CVAL）。 */
final class CConstExpr extends Expr
{
    public function __construct(public readonly string $name)
    {
        parent::__construct();
    }
}
