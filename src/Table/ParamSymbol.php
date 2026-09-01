<?php

declare(strict_types=1);

namespace Tphp\Table;

use Tphp\Ast\Expr;
use Tphp\Token\Pos;

/** 函数参数符号。default 必须是标量字面量（Checker 校验）。 */
final class ParamSymbol
{
    public function __construct(
        public int $type = 0,
        public readonly string $name = '',
        public bool $hasDefault = false,
        public ?Expr $default = null,
        public readonly ?Pos $pos = null,
    ) {}
}
