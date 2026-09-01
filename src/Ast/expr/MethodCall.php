<?php

declare(strict_types=1);

namespace Tphp\Ast\expr;

use Tphp\Ast\Expr;

/** 方法调用：$obj->method(args)。 */
final class MethodCall extends Expr
{
    /** @param list<Expr> $args */
    public function __construct(
        public readonly Expr $obj,
        public readonly string $name,
        public readonly array $args,
    ) {
        parent::__construct();
    }
}
