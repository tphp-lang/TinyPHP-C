<?php

declare(strict_types=1);

namespace Tphp\Token;

/** 源码位置：文件 + 1 基行列号。 */
final class Pos
{
    public function __construct(
        public readonly string $file,
        public readonly int $line,
        public readonly int $col,
    ) {}

    public function __toString(): string
    {
        return "{$this->file}:{$this->line}:{$this->col}";
    }
}
