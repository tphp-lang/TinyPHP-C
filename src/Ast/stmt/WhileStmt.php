<?php

declare(strict_types=1);

namespace Tphp\Ast\stmt;

use Tphp\Ast\Expr;
use Tphp\Ast\Stmt;

/** @param list<Stmt> $body */
final class WhileStmt extends Stmt
{
    public function __construct(
        public readonly Expr $cond,
        public readonly array $body,
    ) {
        parent::__construct();
    }
}
