<?php

declare(strict_types=1);

namespace Tphp\Ast\stmt;

use Tphp\Ast\Node;

use Tphp\Ast\Expr;

/** switch 的一个分支。$cond 为 null 表示 default。 */
final class CaseClause extends Node
{
    /** @param list<Stmt> $stmts */
    public function __construct(
        public readonly ?Expr $cond,
        public readonly array $stmts,
    ) {}
}
