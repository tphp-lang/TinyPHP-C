<?php

declare(strict_types=1);

namespace Tphp\Ast\expr;

use Tphp\Ast\Expr;

/** 浮点字面量（float 32 位或 double，由 Checker 依目标类型确定，字面量默认 double）。 */
final class FloatLit extends Expr
{
    public function __construct(
        public readonly string $text,
        public readonly float $value,
    ) {
        parent::__construct();
    }
}
