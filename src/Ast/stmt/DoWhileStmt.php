<?php

declare(strict_types=1);

namespace Tphp\Ast\stmt;

use Tphp\Ast\Expr;
use Tphp\Ast\Stmt;

/** @param list<Stmt> $body */
final class DoWhileStmt extends Stmt
{
    public function __construct(
        public readonly array $body,
        public readonly Expr $cond,
    ) {
        parent::__construct();
    }
}
