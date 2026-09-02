<?php

declare(strict_types=1);

namespace Tphp\Table;

use Tphp\Ast\Expr;
use Tphp\Token\Pos;

/** 函数参数符号。default 必须是标量字面量（Checker 校验）。 */
final class ParamSymbol
{
    /** 被 use (&$var) 引用捕获：入口落地为堆盒子（Checker 预扫描回填） */
    public bool $boxed = false;

    /** callable 形参收到的闭包签名（checkArgs 从实参回填）：[ret, list<paramType>] */
    public ?array $closureSig = null;

    public function __construct(
        public int $type = 0,
        public readonly string $name = '',
        public bool $hasDefault = false,
        public ?Expr $default = null,
        public readonly ?Pos $pos = null,
    ) {}
}
