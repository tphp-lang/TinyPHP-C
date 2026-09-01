<?php

declare(strict_types=1);

namespace Tphp\Ast\stmt;

use Tphp\Ast\Expr;
use Tphp\Ast\Stmt;

/**
 * if / else if / else。elseif 由 Parser 脱糖为 else 分支里的嵌套 IfStmt。
 *
 * @param list<Stmt> $then
 * @param list<Stmt>|null $else
 */
final class IfStmt extends Stmt
{
    public function __construct(
        public readonly Expr $cond,
        public readonly array $then,
        public readonly ?array $else,
    ) {
        parent::__construct();
    }
}
