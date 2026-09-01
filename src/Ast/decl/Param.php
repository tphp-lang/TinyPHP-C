<?php

declare(strict_types=1);

namespace Tphp\Ast\decl;

use Tphp\Ast\Node;

use Tphp\Ast\Expr;
use Tphp\Ast\TypeRef;

/** 函数/方法参数。$default 必须是标量字面量（Checker 校验）。 */
final class Param extends Node
{
    public function __construct(
        public readonly TypeRef $typeRef,
        public readonly string $name,
        public readonly bool $hasDefault = false,
        public readonly ?Expr $default = null,
    ) {}
}
