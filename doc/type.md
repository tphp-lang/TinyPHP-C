# 类型

| tphp 类型 | C 类型 | 说明 |
| ---- | ---- | ---- |
| int | int32_t | 32 位有符号整数 |
| float | double | 64位浮点（52位尾数）IEEE-754-2008 binary64（PHP float 语义；`double` 为别名） |
| c.f32 | float | 32位浮点（23位尾数）IEEE-754-2008 binary32 |
| string | String | 不可变值类型；SSO ≤23 字节内联，超限走 bump 池 |
| bool | bool | true/false |
| array\<T\> | Array* | 泛型数组（引用类型，见下文） |
| callable | Callable | 闭包 / C 函数指针（双字 {fn, env}；env 为捕获环境，doc/closure.md） |
| void | void | 没有值的数据类型，通常用于函数返回值 |
| 类类型 | tphp_class_X* | 编译期单态化为 C struct，变量为指针，可为 null |
| 接口类型 | TphpIface | Go itab 风格胖指针（对象指针 + 方法表），可为 null |
| null | — | 仅可赋给 array / callable / 类类型（映射 NULL） |
| c.i8 | int8_t | 带符号的8位整数 |
| c.u8 | uint8_t | 无符号8位整数 |
| c.i16 | int16_t | 带符号的16位整数 |
| c.u16 | uint16_t | 无符号16位整数 |
| c.i32 | int32_t | 带符号的32位整数 |
| c.u32 | uint32_t | 无符号32位整数 |
| c.i64 | int64_t | 带符号的64位整数 |
| c.u64 | uint64_t | 无符号的64位整数 |
| c.i128 | __int128 | 带符号的128位整数（后端支持度因编译器而异） |
| c.u128 | unsigned __int128 | 无符号的128位整数（同上） |
| c.char | char | 用于与C语言的ABI兼容性 |
| c.short | short | 同上 |
| c.ushort | unsigned short | 同上 |
| c.uint | unsigned int | 同上 |
| c.long | long | 同上 |
| c.ulong | unsigned long | 同上 |
| c.longlong | long long | 同上 |
| c.ulonglong | unsigned long long | 同上 |
| c.longdouble | long double | 同上 |
| c.f16 | _Float16 | 16位浮点（10位尾数）IEEE-754-2008 binary16（后端支持度因编译器而异） |
| c.f80 | long double | 80位浮点（64位尾数）80位扩展精度 |
| c.f128 | _Float128 | 128位浮点（112位尾数）IEEE-754-2008 binary128（同上） |
| c.f64 | double | 64位浮点的 C 侧别名（与 float 同型） |
| c.ptr | void* | 指针类型 |

## 字符串

不可变值类型：赋值即结构体拷贝，内容永不修改，因此无需引用计数。
`is_local` 区分 SSO 内联与堆分配；`is_lit` 标记 .rodata 字面量（永不释放）。

```c
typedef struct {
    union {
        char *data;
        char  local[STR_SSO_MAX+1]; // SSO inline buffer (when is_local)
    } u;
    int   length;
    bool  is_local;
    bool  is_lit;                // true for .rodata string literals — never free()
} String;
```

## 数组

纯列表：0 起始连续存储，仅整数下标，越界即运行时报错。
没有 PHP 的 map 语义（无字符串键、无稀疏索引）——需要字典时未来提供 `map<K,V>`。
所有 `array<T>` 共享同一个泛型结构（voidptr + element_size 方案），
元素访问由编译器按静态元素类型生成带越界检查的辅助函数调用。

```c
typedef struct {
    int32_t length;
    int32_t capacity;
    int32_t refcount;
    int32_t element_size;
    int32_t elem_flags;     // 0=裸值 1=元素是 Array* 2=元素是对象指针（释放嵌套引用用）
    void *data;
} Array;
```

引用语义：赋值共享同一底层数组（指针拷贝）。

## 内存模型（v0.1）

- 字符串堆内存来自 bump 池，随进程回收；
- 数组 / 对象分配后不主动回收（结构体已含 refcount 字段，运行时已提供 ref/unref 基础设施，后续版本接入自动回收）。

## 类型规则

- 变量显式声明类型，编译期定死：`int $x = 1;`
- 隐式转换只允许数值宽化：int → float（i32 → f64）、c.f32 → float；其余一律显式强转 `(t)$v`（唯一例外：浮点字面量可直接赋给 c.f32——编译期取值，不引入计算误差；float 变量/表达式赋给 c.f32 会损失精度，必须显式 `(c.f32)` 强转）
- `int / int` 按 C 语义整除；`**` 右结合且优先级高于一元负号
- 条件（if / while / for / 三元 / 逻辑运算）要求严格的 bool
- `==` 即恒等（类型编译期固定，无需 `===`）
