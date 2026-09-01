<?php

declare(strict_types=1);

namespace Tphp\Errors;

use Tphp\Token\Pos;

/** 一条编译诊断信息。 */
final class Error
{
    public function __construct(
        public readonly string $message,
        public readonly Pos $pos,
    ) {}
}
