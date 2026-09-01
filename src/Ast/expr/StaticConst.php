<?php

declare(strict_types=1);

namespace Tphp\Ast\expr;

use Tphp\Ast\Expr;

/** 类常量访问：ClassName::NAME / self::NAME / parent::NAME。 */
final class StaticConst extends Expr
{
    public function __construct(
        public readonly string $class,
        public readonly string $name,
    ) {
        parent::__construct();
    }
}
