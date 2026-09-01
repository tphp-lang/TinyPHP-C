<?php

declare(strict_types=1);

namespace Tphp\Ast\decl;

use Tphp\Ast\Node;

use Tphp\Ast\Stmt;
use Tphp\Ast\TypeRef;

/** 顶层函数声明。 */
final class FunctionDecl extends Node
{
    /** @param list<Param> $params @param list<Stmt> $body */
    public function __construct(
        public readonly string $name,
        public readonly array $params,
        public readonly ?TypeRef $ret,
        public readonly array $body,
        public readonly ?string $exportName = null, // #[export("c_name")] 注解的 C 符号名
    ) {}
}
