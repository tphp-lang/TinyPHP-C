<?php

declare(strict_types=1);

namespace Tphp\Ast\expr;

use Tphp\Ast\Expr;

/** 管道占位符：f(a, ...) 中的 ...，标记管道左值的插入位置。仅在管道右侧合法，解析期被替换。 */
final class PlaceholderExpr extends Expr
{
    public function __construct()
    {
        parent::__construct();
    }
}
