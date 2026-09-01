<?php

declare(strict_types=1);

namespace Tphp\Table;

use Tphp\Token\Pos;

/** #struct 登记的 C 结构体（值语义；类型本体由头文件提供，仅编译器布局用）。 */
final class CStructSymbol
{
    /** @var list<array{name: string, type: int}> Checker 解析后的字段（类型码） */
    public array $resolvedFields = [];

    public function __construct(
        public readonly string $name,
        public readonly int $code,
        public readonly ?Pos $pos = null,
    ) {}
}
