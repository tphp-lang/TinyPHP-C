<?php

declare(strict_types=1);

namespace Tphp\Ast\stmt;

use Tphp\Ast\Expr;
use Tphp\Ast\Stmt;

final class ExprStmt extends Stmt
{
    public function __construct(public readonly Expr $expr)
    {
        parent::__construct();
    }
}
