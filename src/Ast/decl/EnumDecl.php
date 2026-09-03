<?php

declare(strict_types=1);

namespace Tphp\Ast\decl;

use Tphp\Ast\Node;
use Tphp\Ast\TypeRef;

/**
 * 枚举类声明（doc/grammar.md）：
 *
 *   enum Suit: string implements HasColor { case Hearts = 'H'; public function ... }
 *
 * backing 为 int/string（backed）或 null（纯枚举）；case 为惰性初始化的单例对象。
 */
final class EnumDecl extends Node
{
    /** @param list<array{name: string, value: ?Expr, pos: \Tphp\Token\Pos}> $cases @param list<ClassMethod> $methods @param list<ClassConstDecl> $consts @param list<string> $implements */
    public function __construct(
        public readonly string $name,
        public readonly ?TypeRef $backing,
        public readonly array $cases,
        public readonly array $methods = [],
        public readonly array $consts = [],
        public readonly array $implements = [],
    ) {}
}
