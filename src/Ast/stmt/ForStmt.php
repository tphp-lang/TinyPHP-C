<?php

declare(strict_types=1);

namespace Tphp\Ast\stmt;

use Tphp\Ast\Expr;
use Tphp\Ast\Stmt;

/**
 * C 式三段 for。$init 是 LocalDecl 或 ExprStmt（均可为 null）。
 *
 * @param list<Stmt> $body
 */
final class ForStmt extends Stmt
{
    public function __construct(
        public readonly ?Stmt $init,
        public readonly ?Expr $cond,
        public readonly ?Expr $post,
        public readonly array $body,
    ) {
        parent::__construct();
    }
}
