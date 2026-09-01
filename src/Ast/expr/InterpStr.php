<?php

declare(strict_types=1);

namespace Tphp\Ast\expr;

use Tphp\Ast\Expr;

/**
 * 双引号字符串（含插值）。
 *
 * @param list<string|Expr> $parts 字面片段（已解析转义）与插值表达式交替
 */
final class InterpStr extends Expr
{
    public function __construct(public readonly array $parts)
    {
        parent::__construct();
    }
}
