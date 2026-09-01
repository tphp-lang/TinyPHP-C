<?php

declare(strict_types=1);

namespace Tphp\Table;

use Tphp\Token\Pos;

/**
 * 接口符号：方法签名集（Go itab 风格的静态接口）。
 *
 * orderedMethods() 给出 itab 的槽位顺序：父接口槽位在前（前缀布局），
 * 本接口新增方法在后——子接口的 itab 指针可安全地按父接口布局复用。
 */
final class InterfaceSymbol
{
    /** @var array<string, FnSymbol> 方法名 → 签名 */
    public array $methods = [];

    /** @var list<InterfaceSymbol> 已解析的父接口 */
    public array $extends = [];

    public function __construct(
        public readonly string $name,
        public readonly int $code,
        public readonly ?Pos $pos = null,
    ) {}

    /** 沿 extends 链查找方法签名。 */
    public function findMethod(string $name): ?FnSymbol
    {
        if (isset($this->methods[$name])) {
            return $this->methods[$name];
        }
        foreach ($this->extends as $parent) {
            $fn = $parent->findMethod($name);
            if ($fn !== null) {
                return $fn;
            }
        }
        return null;
    }

    /** itab 槽位顺序：父接口在前、本接口新增在后（去重）。 */
    public function orderedMethods(): array
    {
        $out = [];
        foreach ($this->extends as $parent) {
            foreach ($parent->orderedMethods() as $name => $fn) {
                $out[$name] = $fn;
            }
        }
        foreach ($this->methods as $name => $fn) {
            $out[$name] ??= $fn;
        }
        return $out;
    }

    /** 自身及全部祖先接口（去重）。 */
    public function extendsClosure(): array
    {
        $out = [$this->name => $this];
        foreach ($this->extends as $parent) {
            foreach ($parent->extendsClosure() as $name => $iface) {
                $out[$name] ??= $iface;
            }
        }
        return $out;
    }

    public function isSubinterfaceOf(self $other): bool
    {
        if ($this === $other) {
            return true;
        }
        foreach ($this->extends as $parent) {
            if ($parent->isSubinterfaceOf($other)) {
                return true;
            }
        }
        return false;
    }
}
