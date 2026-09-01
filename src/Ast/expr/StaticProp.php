<?php

declare(strict_types=1);

namespace Tphp\Ast\expr;

use Tphp\Ast\Expr;

/** 静态属性：ClassName::$name / self::$name / parent::$name。 */
final class StaticProp extends Expr
{
    public function __construct(
        public readonly string $class,
        public readonly string $name,
    ) {
        parent::__construct();
    }
}
