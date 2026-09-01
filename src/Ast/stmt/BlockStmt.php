<?php

declare(strict_types=1);

namespace Tphp\Ast\stmt;

use Tphp\Ast\Stmt;

/** 裸块语句：{ ... }（引入新的词法作用域）。 */
final class BlockStmt extends Stmt
{
    /** @param list<Stmt> $stmts */
    public function __construct(public readonly array $stmts)
    {
        parent::__construct();
    }
}
