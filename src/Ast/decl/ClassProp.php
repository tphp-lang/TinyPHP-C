<?php

declare(strict_types=1);

namespace Tphp\Ast\decl;

use Tphp\Ast\Node;

use Tphp\Ast\Expr;
use Tphp\Ast\TypeRef;

/** 类属性。$default 仅允许标量/null 字面量。 */
final class ClassProp extends Node
{
    public function __construct(
        public readonly string $vis,
        public readonly bool $isStatic,
        public readonly TypeRef $typeRef,
        public readonly string $name,
        public readonly bool $hasDefault,
        public readonly ?Expr $default,
    ) {}
}
