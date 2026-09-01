<?php

declare(strict_types=1);

namespace Tphp\Ast\expr;

use Tphp\Ast\Expr;

final class ThisExpr extends Expr
{
    public function __construct()
    {
        parent::__construct();
    }
}
