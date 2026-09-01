<?php

declare(strict_types=1);

namespace Tphp\Ast\decl;

use Tphp\Ast\TypeRef;

/** 接口方法签名（无方法体）。 */
final class InterfaceMethod
{
    /** @param list<Param> $params */
    public function __construct(
        public readonly string $name,
        public readonly array $params,
        public readonly ?TypeRef $ret,
    ) {}
}
