<?php

declare(strict_types=1);

namespace Tphp\Ast\expr;

use Tphp\Ast\Expr;

/** 字符串字面量，$value 为已解析转义的原始字节。 */
final class StrLit extends Expr
{
    public function __construct(public readonly string $value)
    {
        parent::__construct();
    }
}
