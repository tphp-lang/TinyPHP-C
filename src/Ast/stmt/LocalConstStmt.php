<?php

declare(strict_types=1);

namespace Tphp\Ast\stmt;

use Tphp\Ast\Expr;
use Tphp\Ast\Stmt;
use Tphp\Ast\TypeRef;
use Tphp\Type\Type;

/** 函数内常量：const [TYPE] NAME = 字面量; */
final class LocalConstStmt extends Stmt
{
    /** Checker 回填的已解析类型码。 */
    public int $varType = Type::NONE;

    public function __construct(
        public readonly string $name,
        public readonly ?TypeRef $typeRef,
        public readonly Expr $value,
    ) {
        parent::__construct();
    }
}
