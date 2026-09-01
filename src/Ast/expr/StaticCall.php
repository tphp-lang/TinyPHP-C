<?php

declare(strict_types=1);

namespace Tphp\Ast\expr;

use Tphp\Ast\Expr;

/**
 * 静态方法调用：ClassName::method(args) / self::method(args) / parent::method(args)。
 * self/parent 在 Checker 解析到具体类。
 */
final class StaticCall extends Expr
{
    /** @param list<Expr> $args */
    public function __construct(
        public readonly string $class,
        public readonly string $method,
        public readonly array $args,
    ) {
        parent::__construct();
    }
}
