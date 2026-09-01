<?php

declare(strict_types=1);

namespace Tphp\Table;

use Tphp\Ast\Expr;
use Tphp\Token\Pos;

/**
 * 常量符号（与旧版机制一致）：
 * 顶层常量 / 类常量 / 函数内常量共用；类常量带 vis 与 owner。
 * C 生成：#define TPHP_CONST_<UPPER>（顶层）、#define TPHP_CONST_<CLASS>_<UPPER>（类）。
 */
final class ConstSymbol
{
    public function __construct(
        public readonly string $name,
        public int $type = 0,
        public readonly ?Expr $value = null,
        public string $vis = 'public',
        public ?ClassSymbol $owner = null,
        public readonly ?Pos $pos = null,
    ) {}
}
