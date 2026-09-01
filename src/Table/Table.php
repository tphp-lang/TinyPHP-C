<?php

declare(strict_types=1);

namespace Tphp\Table;

use Tphp\Type\Type;

/**
 * 全局符号表。
 *
 * 持有全部类型信息：内置类型、c.* 别名、array<T> 实例、类。
 * Checker 与 Gen 之间只传递 int 类型和这张表——没有任何平行的类型定义。
 */
final class Table
{
    /** @var array<int, TypeKind> 类型码 → 种类 */
    public array $kinds = [];

    /** @var array<int, string> 类型码 → C 类型名（数组为 Array*，类为 tphp_class_X*） */
    public array $cnames = [];

    /** @var array<string, int> 类型名 → 类型码 */
    public array $byName = [];

    /** @var array<int, int> array<T> 码 → 元素类型码 */
    public array $arrayElems = [];

    /** @var array<string, ClassSymbol> 类名 → 符号 */
    public array $classes = [];

    /** @var array<string, InterfaceSymbol> 接口名 → 符号 */
    public array $ifaces = [];

    /** @var array<string, CStructSymbol> #struct 名 → 符号 */
    public array $cstructs = [];

    /** @var array<string, int> C 指针类型缓存：'T*' → 类型码 */
    private array $pointers = [];

    /** @var array<string, FnSymbol> 函数名 → 符号（含内置 len/dump） */
    public array $fns = [];

    /** @var array<string, ConstSymbol> 顶层常量名 → 符号（FQ 名） */
    public array $consts = [];

    /** @var array<string, string> 已占用的 C 符号名 → PHP 名（跨命名空间查重） */
    public array $cNames = [];

    /** 注册 C 符号名，冲突返回 false（由 Checker 报错）。 */
    public function registerCSymbol(string $cName, string $phpName): bool
    {
        if (isset($this->cNames[$cName])) {
            return false;
        }
        $this->cNames[$cName] = $phpName;
        return true;
    }

    private int $nextClass = Type::I_CLASS_BASE;
    private int $nextCtype = Type::I_CTYPE_BASE;

    public function __construct()
    {
        foreach (Type::BUILTIN_NAMES as $code => $name) {
            $this->registerBuiltin($code, $name);
        }
        // 'double' 是 float（f64）的别名（PHP (double) 强转习惯）
        $this->byName['double'] = Type::I_DOUBLE;
        $this->cnames[Type::I_STRING] = 'String';
        $this->cnames[Type::I_ARRAY] = 'Array';
        $this->cnames[Type::I_NULL] = 'void*'; // null 类型 = C 空指针

        // c.* 别名（doc/type.md）
        foreach (Type::C_ALIASES as $name => $cname) {
            $code = $this->nextCtype++;
            $this->kinds[$code] = TypeKind::Ctype;
            $this->cnames[$code] = $cname;
            $this->byName[$name] = $code;
        }

        // 内置极小函数集
        $this->fns['len'] = new FnSymbol('len', isBuiltin: true);
        $this->fns['var_dump'] = new FnSymbol('var_dump', isBuiltin: true);
        // phpc 桥接：string ↔ char* + C 内存所有权
        $this->fns['c_str'] = new FnSymbol('c_str', isBuiltin: true);
        $this->fns['php_str'] = new FnSymbol('php_str', isBuiltin: true);
        $this->fns['php_str_ref'] = new FnSymbol('php_str_ref', isBuiltin: true);
        $this->fns['c_own'] = new FnSymbol('c_own', isBuiltin: true);
        $this->fns['cbuf'] = new FnSymbol('cbuf', isBuiltin: true);
    }

    private function registerBuiltin(int $code, string $name): void
    {
        $this->kinds[$code] = TypeKind::Builtin;
        $this->byName[$name] = $code;
        // C 类型名按代码映射：float（f64）的 C 类型是 double，c.f32（f32）的 C 类型是 float
        $this->cnames[$code] = match ($code) {
            Type::I_INT => 'int32_t',
            Type::I_FLOAT => 'float',
            Type::I_DOUBLE => 'double',
            Type::I_BOOL => 'bool',
            Type::I_VOID => 'void',
            Type::I_CALLABLE => 'Callable',
            default => $name,
        };
    }

    // ------------------------------------------------------------- 类型操作

    /** 按名字解析内置类型 / c.* 别名（不含类与 array<T>）。 */
    public function findNamed(string $name): ?int
    {
        return $this->byName[$name] ?? null;
    }

    /** 取出（或注册）array<T> 类型码，同一元素类型只注册一次。 */
    public function arrayOf(int $elem): int
    {
        if ($elem === Type::NONE) {
            return Type::I_ARRAY;
        }
        $key = 'array<' . $elem . '>';
        if (isset($this->byName[$key])) {
            return $this->byName[$key];
        }
        $code = $this->nextCtype++;
        $this->kinds[$code] = TypeKind::ArrayOf;
        $this->cnames[$code] = 'Array';
        $this->arrayElems[$code] = $elem;
        $this->byName[$key] = $code;
        return $code;
    }

    public function arrayElemOf(int $code): int
    {
        return $this->arrayElems[$code] ?? Type::NONE;
    }

    // ------------------------------------------------------------- 分类判定

    public function kindOf(int $code): TypeKind
    {
        return $this->kinds[$code] ?? TypeKind::Builtin;
    }

    public function isIntLike(int $code): bool
    {
        if ($code === Type::I_INT) {
            return true;
        }
        if ($this->kindOf($code) !== TypeKind::Ctype) {
            return false;
        }
        $name = array_search($code, $this->byName, true);
        return is_string($name) && in_array($name, Type::C_INT_ALIASES, true);
    }

    public function isFloatLike(int $code): bool
    {
        if ($code === Type::I_FLOAT || $code === Type::I_DOUBLE) {
            return true;
        }
        if ($this->kindOf($code) !== TypeKind::Ctype) {
            return false;
        }
        $name = array_search($code, $this->byName, true);
        return is_string($name) && in_array($name, Type::C_FLOAT_ALIASES, true);
    }

    public function isNumeric(int $code): bool
    {
        return $this->isIntLike($code) || $this->isFloatLike($code);
    }

    /** 标量：可隐式转字符串的值（echo / . 拼接）。 */
    public function isScalar(int $code): bool
    {
        return $this->isNumeric($code) || $code === Type::I_BOOL || $code === Type::I_STRING;
    }

    public function isString(int $code): bool
    {
        return $code === Type::I_STRING;
    }

    public function isBool(int $code): bool
    {
        return $code === Type::I_BOOL;
    }

    public function isVoid(int $code): bool
    {
        return $code === Type::I_VOID;
    }

    public function isArray(int $code): bool
    {
        return $this->kindOf($code) === TypeKind::ArrayOf;
    }

    public function isClass(int $code): bool
    {
        return $this->kindOf($code) === TypeKind::ClassType;
    }

    public function isCallable(int $code): bool
    {
        return $code === Type::I_CALLABLE;
    }

    /** 引用类型：可以赋 null 的类型（指针/胖指针语义）。 */
    public function isRefType(int $code): bool
    {
        return $this->isArray($code)
            || $this->isClass($code)
            || $this->isInterface($code)
            || $this->isCallable($code)
            || $code === Type::I_NULL;
    }

    public function className(int $code): string
    {
        if (!$this->isClass($code)) {
            return '';
        }
        foreach ($this->classes as $class) {
            if ($class->code === $code) {
                return $class->name;
            }
        }
        return '';
    }

    /** 分配一个类类型码。 */
    public function allocClassCode(): int
    {
        return $this->nextClass++;
    }

    /** 注册类符号到类型表。 */
    public function addClass(ClassSymbol $class): void
    {
        $this->classes[$class->name] = $class;
        $this->kinds[$class->code] = TypeKind::ClassType;
        $this->cnames[$class->code] = 'tphp_class_' . Type::mangleName($class->name);
        $this->byName[$class->name] = $class->code;
    }

    /** 分配接口类型码并注册接口符号。 */
    public function addInterface(InterfaceSymbol $iface): void
    {
        $this->ifaces[$iface->name] = $iface;
        $this->kinds[$iface->code] = TypeKind::InterfaceType;
        $this->cnames[$iface->code] = 'TphpIface';
        $this->byName[$iface->name] = $iface->code;
    }

    public function isInterface(int $code): bool
    {
        return $this->kindOf($code) === TypeKind::InterfaceType;
    }

    /** 注册 #struct。 */
    public function addCStruct(CStructSymbol $struct): void
    {
        $this->cstructs[$struct->name] = $struct;
        $this->kinds[$struct->code] = TypeKind::CStruct;
        $this->cnames[$struct->code] = $struct->name;
        $this->byName[$struct->name] = $struct->code;
    }

    /** 取出（或注册）C 指针类型 T*。cname 使用基础类型的 C 名。 */
    public function pointerOf(string $baseName, ?int $baseCode = null): int
    {
        $key = $baseName . '*';
        if (isset($this->pointers[$key])) {
            return $this->pointers[$key];
        }
        $code = $this->nextCtype++;
        $cBase = $baseCode !== null && isset($this->cnames[$baseCode])
            ? $this->cnames[$baseCode]
            : Type::mangleName($baseName);
        // string 借用不可写：c.char* 以 const char* 表示
        if ($cBase === 'char') {
            $cBase = 'const char';
        }
        $this->kinds[$code] = TypeKind::CPointer;
        $this->cnames[$code] = $cBase . '*';
        $this->byName[$key] = $code;
        $this->pointers[$key] = $code;
        return $code;
    }

    public function isCStruct(int $code): bool
    {
        return $this->kindOf($code) === TypeKind::CStruct;
    }

    public function isCPointer(int $code): bool
    {
        return $this->kindOf($code) === TypeKind::CPointer;
    }

    public function interfaceByCode(int $code): ?InterfaceSymbol
    {
        return $this->isInterface($code)
            ? array_find($this->ifaces, fn ($i) => $i->code === $code)
            : null;
    }

    public function classByCode(int $code): ?ClassSymbol
    {
        return $this->isClass($code)
            ? array_find($this->classes, fn ($c) => $c->code === $code)
            : null;
    }

    /** 数组元素种类：0=裸值 1=嵌套数组 2=对象 3=接口胖指针（供运行时释放逻辑用）。 */
    public function arrayElemFlags(int $code): int
    {
        $elem = $this->arrayElemOf($code);
        if ($this->isArray($elem)) {
            return 1;
        }
        if ($this->isClass($elem)) {
            return 2;
        }
        if ($this->isInterface($elem)) {
            return 3;
        }
        return 0;
    }

    /** 语言中的类型名（用于错误信息与 dump）。 */
    public function displayName(int $code): string
    {
        if ($code === Type::NONE) {
            return '<unknown>';
        }
        $found = array_search($code, $this->byName, true);
        if (is_string($found) && $found !== '' && !str_starts_with($found, 'array<')) {
            return $found;
        }
        if ($this->isArray($code)) {
            return 'array<' . $this->displayName($this->arrayElemOf($code)) . '>';
        }
        if ($this->isClass($code)) {
            return $this->className($code);
        }
        return Type::BUILTIN_NAMES[$code] ?? '<unknown>';
    }
}
