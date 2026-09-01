<?php

declare(strict_types=1);

namespace Tphp\Ast\expr;

use Tphp\Ast\Expr;

/** 整数字面量。$text 是可直接进入 C 的字面文本（0x 保持原样，0b/0o 已转十进制）。 */
final class IntLit extends Expr
{
    public function __construct(
        public readonly string $text,
        public readonly int $value,
    ) {
        parent::__construct();
    }
}
