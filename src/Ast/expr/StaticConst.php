<?php

declare(strict_types=1);

namespace Tphp\Ast\expr;

use Tphp\Ast\Expr;

/** 类常量访问：ClassName::NAME / self::NAME / parent::NAME；命中枚举 case 时 isEnumCase 由 Checker 回填。 */
final class StaticConst extends Expr
{
    public bool $isEnumCase = false;

    public function __construct(
        public readonly string $class,
        public readonly string $name,
    ) {
        parent::__construct();
    }
}
