<?php

declare(strict_types=1);

namespace Tphp\Ast\decl;

use Tphp\Ast\Node;

/** 接口声明：方法签名集。 */
final class InterfaceDecl extends Node
{
    /** @param list<string> $extends @param list<InterfaceMethod> $methods */
    public function __construct(
        public readonly string $name,
        public readonly array $extends,
        public readonly array $methods,
    ) {
        parent::__construct();
    }
}
