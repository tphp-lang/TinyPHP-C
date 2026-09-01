<?php

declare(strict_types=1);

namespace Tphp\Ast\stmt;

use Tphp\Ast\Expr;
use Tphp\Ast\Stmt;

/**
 * switch 语句（PHP 语义：case 之间不隐式穿透，用 break 跳出）。
 *
 * @param list<CaseClause> $cases
 */
final class SwitchStmt extends Stmt
{
    public function __construct(
        public readonly Expr $cond,
        public readonly array $cases,
    ) {
        parent::__construct();
    }
}
