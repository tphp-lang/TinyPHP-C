<?php

declare(strict_types=1);

namespace Tphp\Ast\decl;

use Tphp\Ast\Node;
use Tphp\Ast\TypeRef;

/** #struct 登记的 C 结构体布局（类型本体由 #include 的头文件提供，不生成 C）。 */
final class CStructDecl extends Node
{
    /** @param list<array{type: TypeRef, name: string}> $fields */
    public function __construct(
        public readonly string $name,
        public readonly array $fields,
    ) {
        parent::__construct();
    }
}
