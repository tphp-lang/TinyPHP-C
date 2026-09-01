<?php

declare(strict_types=1);

namespace Tphp\Ast\expr;

use Tphp\Ast\Expr;

/** 对象构造：new ClassName(args)。 */
final class NewExpr extends Expr
{
    /** @param list<Expr> $args */
    public function __construct(
        public readonly string $class,
        public readonly array $args,
    ) {
        parent::__construct();
    }
}
