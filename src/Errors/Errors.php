<?php

declare(strict_types=1);

namespace Tphp\Errors;

use Tphp\Token\Pos;

/**
 * 统一的诊断收集器。
 *
 * Scanner / Parser / Checker 全部往这里报告，由 Builder 统一格式化输出。
 * 收集到多条错误后一次性呈现，而不是报一条停一条。
 * 警告（warn）不阻断编译，单独收集、单独呈现。
 */
final class Errors
{
    /** @var list<Error> */
    private array $items = [];

    /** @var list<Error> */
    private array $warnings = [];

    public function add(string $message, Pos $pos): void
    {
        $this->items[] = new Error($message, $pos);
    }

    public function warn(string $message, Pos $pos): void
    {
        $this->warnings[] = new Error($message, $pos);
    }

    public function hasErrors(): bool
    {
        return $this->items !== [];
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function warningCount(): int
    {
        return count($this->warnings);
    }

    /** @return list<Error> */
    public function all(): array
    {
        return $this->items;
    }

    /** 按文件与位置排序后格式化为多行文本。 */
    public function report(): string
    {
        $lines = [];
        foreach ($this->items as $error) {
            $lines[] = "{$error->pos}: error: {$error->message}";
        }
        return implode("\n", $lines) . "\n";
    }

    /** 格式化全部警告（编译成功时也可能有警告）。 */
    public function reportWarnings(): string
    {
        $lines = [];
        foreach ($this->warnings as $warning) {
            $lines[] = "{$warning->pos}: warning: {$warning->message}";
        }
        return $lines === [] ? '' : implode("\n", $lines) . "\n";
    }
}
