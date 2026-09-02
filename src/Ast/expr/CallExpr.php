<?php

declare(strict_types=1);

namespace Tphp\Ast\expr;

use Tphp\Ast\Expr;

/** 具名函数调用（含内置 len/dump）。retClosureSig：返回闭包时由 Checker 回填；closureSig：c_fn 包装的闭包签名。 */
final class CallExpr extends Expr
{
    /** @var array{ret: int, params: list<int>}|null */
    public ?array $retClosureSig = null;

    /** @var array{ret: int, params: list<int>}|null */
    public ?array $closureSig = null;

    /** @param list<Expr> $args */
    public function __construct(
        public string $name,
        public readonly array $args,
    ) {
        parent::__construct();
    }
}
