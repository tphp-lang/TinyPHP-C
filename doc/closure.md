# 闭包（Closure）设计与实施计划

状态：**设计定稿，待实施**（v0.3.1 目标特性）。
调研基准：旧版 TinyPHP（C:\project\php\TinyPHP，已完整实现）、V 0.5.2（doc/docs.md 匿名函数章节）。

## 一、目标

- PHP 风格闭包字面量：`function (T $p): T use ($a, &$b) { ... }`
- 箭头糖：`fn (T $p): T => expr`（自动按值捕获体内自由变量）
- 闭包调用：`$f(args)`
- **按值捕获**（PHP `use` 默认语义）与**按引用捕获**（`use (&$var)`，内外共享存储）
- 与既有特性无缝整合：管道 `|>`、`or` 错误块、`--mem-stats leaks=0`

## 二、调研结论

| | 旧版 TinyPHP | V 0.5.2 | 本设计 |
|----|----|----|----|
| 闭包表示 | `t_callback {func, env}` 双字胖指针 | 函数 + 捕获结构体 | 复用现有 `Callable {fn, env}`（已预留） |
| 捕获语法 | `use ($a, $b)` 显式；`fn =>` 自动按值 | `fn [i]` 显式捕获表 | 同旧版 |
| 引用捕获 | 未做 | 需显式取指针 `fn [ref]` | **盒子（box）机制**，见 §3.5 |
| 环境 | `_cap_N` 结构体 + thunk | 编译器内联提升 | `_env_N` 结构体 + thunk（同旧版） |
| 调用 | `closureSigs` 记签名 → 强转函数指针 | 直接调用 | `VarSymbol` 记签名 → 强转 |
| 环境生命周期 | `tphp_rt_register(env, 5)` 退出统一回收 | 引用计数 | 登记退出回收（同旧版）+ 退出析构 |

两个参考项目在"函数指针 + 捕获结构体"路线上互相印证；引用捕获的成本（需要间接层）
也被 V 的 `fn [ref]` 印证。旧版实现可直接作为移植蓝图。

## 三、设计

### 3.1 语法（文法增量）

```
primary  = ... | closure | "fn", "(", [params], ")", [":", type], "=>" ( expr | block ) ;

closure  = "function", "(", [params], ")", [ "use", "(", capture-list, ")" ],
           [":", type], block ;
capture  = ["&"], var ;    (* & = 按引用捕获 *)
```

- PHP 次序：参数 → `use` → `: 返回类型` → 函数体
- `fn` 新增关键字（Scanner 词法表 + TokenKind::KwFn）
- 返回类型可省略：块体闭包省略 = `void`；箭头闭包省略 = 按表达式推断

```php
$add = function (int $a, int $b): int { return $a + $b; };
$inc = fn (int $v): int => $v + 1;
$g = function (int $x) use ($prefix, &$total): string { ... };
```

### 3.2 类型系统

- **不引入泛型函数类型**：闭包类型就是现有 `callable`（内建类型，零值/null 路径已通）
- 闭包的**签名**（参数类型表 + 返回类型）不进类型码，而是记在变量符号上：
  `VarSymbol` 新增 `?array $closureSig`（`[ret, list<paramType>]`）
- 赋值/初始化右侧是 `ClosureExpr` 时写入签名；声明 `callable $f` 未赋闭包时签名为 null
- 调用 `$f(x)` 按 `$f` 的签名做 checkArgs；签名为 null → 编译错误"callable 变量缺少可推导签名"

### 3.3 AST（一节点一文件）

- `src/Ast/expr/ClosureExpr.php`：params / ret / body / captures（name, byRef）/ 解析期空位
  （Checker 回填 `resolvedCaptures`：每项 {name, byRef, type}；`sig`）
- `src/Ast/expr/InvokeExpr.php`：`$f(args)` —— callee（VarExpr）+ args
- 箭头闭包 `=> expr` 在 Parser 里包装为 `[new ReturnStmt(expr)]`，与块体同构

### 3.4 各层改动

| 层 | 改动 |
|----|----|
| Scanner | `fn` 关键字 → KwFn |
| Parser | 表达式位 `function (` → parseClosure；`fn (` → parseArrow；postfix 尾部 VarExpr + `(` → InvokeExpr；管道 `pipeInto` 增加 InvokeExpr 分支 |
| Checker | checkClosure（参数/返回/捕获解析/新作用域查体）；箭头自由变量扫描（body 内 VarExpr 名 - 参数名 → 自动捕获）；InvokeExpr 按 VarSymbol 签名校验；赋值/初始化写入 closureSig |
| Gen | 新增 `captypes`/`closures` 两节；闭包体生成（捕获上下文栈）；Invoke 强转调用；盒子变量存取重写（§3.5）；env 析构注册 |
| runtime | `tphp_callable.h` 增加环境登记表：`tphp_env_alloc(size, dtor)` / `tphp_env_free_all()`（atexit） |

### 3.5 按引用捕获：盒子机制

问题：`use (&$x)` 要求内外共享同一存储，而变量在生成的 C 里是栈局部；
闭包可能逃逸（返回/存入数组），取栈地址会产生悬空——不可接受（本项目承诺内存安全）。

方案：**被引用捕获的变量，其存储提升为堆上的"盒子"**，内外所有读写经盒子解引用：

```
外层函数栈                  堆
┌─────────────────┐
│ void *n_box ────┼───> ┌─────────────┐      ┌──────────────┐
└─────────────────┘     │ box: int n  │ <────┤ env.n_box    │ 闭包 env
        ↑ 内外读写        │ (tphp_env_alloc)   └──────────────┘
        都解引用盒子      └─────────────┘      （同一盒子指针，共享读写）
```

- 盒子按变量类型定尺寸：`tphp_env_alloc(sizeof(T), box_dtor)`；读写文本 = `(*(T*)n_box)`
- 盒子在**声明点/参数入口**创建（内容零值初始化）；生命周期 = 进程退出统一回收（与 env 同策略），
  堆类型内容的 decref 由 box_dtor 完成
- 同一变量被多个闭包引用捕获 → 共享同一盒子（真·共享可变状态，计数器场景成立）
- 赋值插桩复用现有 `rcAssignText`：把盒子的解引用文本当作变量的 lvalue 注册进 rcScope

### 3.6 内存管理

- env/box 全部走 `tphp_env_alloc` 登记，`atexit(tphp_env_free_all)` 统一回收；
  登记块不进 arrays/objects 计数器 → `--mem-stats leaks=0` 断言不受影响
- **env 析构**：捕获了 array/object/iface（按值）的闭包生成 `_env_dtor_N` 递减引用；
  by-ref 盒子字段无需析构（盒子自析构）
- **盒子析构**：堆类型内容 decref；标量/string（池）无操作
- atexit 顺序：`tphp_env_free_all` 必须先于 `tphp_mem_report` 执行（env 析构会递减
  数组/对象引用 → 先释放后统计）。atexit 后注册先执行 → Gen 在 main 体首条语句 emit
  `atexit(tphp_env_free_all);`（位于 --mem-stats 注入行之后）
- string 捕获：结构体拷贝（池内存，无引用计数）——零成本

### 3.7 捕获上下文（Gen 实现要点）

生成闭包体时压入"捕获上下文"（仿现有 `curClassSym` 的栈模式）：

- 按值捕获名 → lvalue `(((_env_N*)_env)->x)`，登记进 rcScope（类型 = 捕获类型）；
  闭包体作用域退出**不**清理 env 字段（env 自身持有到退出）
- 按引用捕获名 → lvalue `(*(T*)(((_env_N*)_env)->x_box))`
- 未捕获的名字 = 闭包自身局部/参数，正常生成

### 3.8 与既有特性整合

- 管道：`8 |> $inc()` —— `pipeInto` 增加 InvokeExpr 分支（callee 是 VarExpr，天然"简单接收者"）
- `or` 错误块：`$x |> $f() or { ... }` —— OrExpr 的 call 是表达式级检查，Invoke 天然兼容
- `var_dump($f)`：打印 `callable`（现有占位）

## 四、边界（v1 明确不做，全部编译期报错）

| 特性 | 理由 |
|----|----|
| `use (&$this)` / 闭包内 `$this` | $this 绑定需要对象 env，v1 提示"方法内闭包暂不支持 \$this" |
| 首类可调用 `f(...)` / `A::m(...)` | 独立特性，后续版本 |
| 静态函数引用直接赋给 callable | 同上 |
| 传递引用捕获（外层按值捕获的变量再被内层引用捕获） | 盒子提升需跨层传播，v1 报错 |
| 生成器（旧版 isGen 字段） | 不在路线图 |

## 五、实施步骤（每步可编译验收）

1. **runtime**：`tphp_callable.h` 环境登记表 + free_all（独立可测）
2. **语法层**：TokenKind::KwFn → Scanner → AST 两节点 → Parser（闭包/箭头/调用/管道分支）
3. **Checker**：捕获解析、自由变量扫描、签名写入 VarSymbol、InvokeExpr 校验
4. **Gen**：capTypes/closures 两节、捕获上下文、盒子、Invoke 强转、main 首语句 atexit
5. **测试**：`tests/cases/20_closure.php`（全部特性 + makeCounter 引用捕获逃逸）
   + `tests/cases/21_closure_err.php`（expect-error：捕获未定义变量 / 调用无签名 callable / $this）
6. **文档**：README 语言概览、type.md callable 行更新、grammar.md 文法、function.md 不动

## 六、验收标准

- `php tests/run.php` 全绿（含新增用例），`--mem-stats leaks=0`
- 计数器用例（引用捕获 + 闭包逃逸返回）：两次调用返回 1、2 —— 证明盒子共享与逃逸存活
- 管道 + 闭包：`8 |> $inc()` 正常
- 交叉编译产物（linux arm64）可正常生成

## 七、风险

| 风险 | 对策 |
|----|----|
| TCC(Win64) 结构体按值调用临时槽复用（tphp_str_eq 教训） | 闭包体与调用点生成代码遵守"同一表达式至多一次结构体按值调用"；env 字段一律 LVALUE 直读 |
| env 析构与 mem 统计时序 | atexit 后注册先执行的顺序保证（§3.6） |
| 捕获变量重映射与 RC 作用域栈交互 | 盒子/env 字段以"非作用域清理"方式登记（新增登记旗标），作用域退出不递减 |
