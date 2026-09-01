<?php

declare(strict_types=1);

namespace Tphp\Token;

/** 一个词法单元：种类 + 原文 + 位置。 */
final class Token
{
    public function __construct(
        public readonly TokenKind $kind,
        public readonly string $lit,
        public readonly Pos $pos,
    ) {}
}
