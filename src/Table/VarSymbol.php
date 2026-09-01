<?php

declare(strict_types=1);

namespace Tphp\Table;

use Tphp\Ast\Expr;
use Tphp\Token\Pos;
/**
 * 变量符号：局部变量 / 参数 / 类属性共用。
 *
 * vis / isStatic / default 只对类属性有意义。
 */
final class VarSymbol
{
    public ?ClassSymbol $owner = null;

    public function __construct(
        public readonly string $name,
        public int $type = 0,
        public readonly ?Pos $pos = null,
        public string $vis = 'public',
        public bool $isStatic = false,
        public bool $hasDefault = false,
        public ?Expr $default = null,
    ) {}
}
