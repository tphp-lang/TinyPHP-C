<?php

declare(strict_types=1);

namespace Tphp\Ast\stmt;

use Tphp\Ast\Expr;
use Tphp\Ast\Stmt;
use Tphp\Ast\TypeRef;
use Tphp\Type\Type;

/** 局部变量声明：int $x = 1; / array<int> $a;（未初始化为零值）。 */
final class LocalDecl extends Stmt
{
    /** Checker 回填的已解析类型码，Gen 直接使用。 */
    public int $varType = Type::NONE;

    /** 被 use (&$var) 引用捕获：声明落地为堆盒子（Checker 回填） */
    public bool $boxed = false;

    public function __construct(
        public readonly TypeRef $typeRef,
        public readonly string $name,
        public readonly ?Expr $init,
    ) {
        parent::__construct();
    }
}
