<?php

declare(strict_types=1);

namespace Tphp\Ast\expr;

use Tphp\Ast\Expr;
use Tphp\Ast\Stmt;
use Tphp\Ast\TypeRef;
use Tphp\Ast\decl\Param;

/**
 * 闭包字面量（doc/closure.md）：
 *
 *   function (T $p): T use ($a, &$b) { ... }
 *   fn (T $p): T => expr        （箭头糖；解析期包装为单 return 块体）
 *
 * 捕获项：name + byRef（&$x 按引用捕获，经堆盒子共享存储）。
 * resolvedCaptures / sig 由 Checker 回填；Gen 只消费标注结果。
 */
final class ClosureExpr extends Expr
{
    /** @param list<Param> $params @param list<Stmt> $body @param list<array{name: string, byRef: bool}> $captures */
    public function __construct(
        public readonly array $params,
        public readonly ?TypeRef $ret,
        public readonly array $body,
        public readonly array $captures = [],
        public readonly bool $isArrow = false,
    ) {}

    /** @var list<array{name: string, byRef: bool, type: int}> Checker 回填 */
    public array $resolvedCaptures = [];

    /** @var array{ret: int, params: list<int>}|null Checker 回填：闭包签名 */
    public ?array $sig = null;

    /** Gen 回填：闭包编号（env 结构体 / thunk 命名用） */
    public ?int $closureId = null;

    /** 一等可调用适配器：f(...) → fn(arg0) => f(arg0)，值为函数名（Checker 从函数表推导签名） */
    public ?string $adapterOf = null;
}
