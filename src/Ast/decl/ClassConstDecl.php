<?php

declare(strict_types=1);

namespace Tphp\Ast\decl;

use Tphp\Ast\Expr;
use Tphp\Ast\Node;
use Tphp\Ast\TypeRef;

/** 类常量：[vis] const TYPE name = 字面量;（类型必填，与旧版机制一致）。 */
final class ClassConstDecl extends Node
{
    public function __construct(
        public readonly string $vis,
        public readonly TypeRef $typeRef,
        public readonly string $name,
        public readonly Expr $value,
    ) {
        parent::__construct();
    }
}
