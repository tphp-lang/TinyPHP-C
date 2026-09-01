<?php

declare(strict_types=1);

namespace Tphp\Ast\decl;

use Tphp\Ast\Expr;
use Tphp\Ast\Node;
use Tphp\Ast\TypeRef;

/** 顶层常量：const [TYPE] NAME = 字面量;（类型注解可选，与旧版机制一致）。 */
final class ConstDecl extends Node
{
    public function __construct(
        public readonly string $name,
        public readonly ?TypeRef $typeRef,
        public readonly Expr $value,
    ) {
        parent::__construct();
    }
}
