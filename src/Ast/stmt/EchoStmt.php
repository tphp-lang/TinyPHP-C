<?php

declare(strict_types=1);

namespace Tphp\Ast\stmt;

use Tphp\Ast\Expr;
use Tphp\Ast\Stmt;

/** echo 语句（语言关键字，非内置函数）。 */
final class EchoStmt extends Stmt
{
    /** @param list<Expr> $parts */
    public function __construct(public readonly array $parts)
    {
        parent::__construct();
    }
}
