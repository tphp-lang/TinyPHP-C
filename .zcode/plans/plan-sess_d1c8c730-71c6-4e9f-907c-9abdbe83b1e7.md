# TinyPHP v0.5：类型自动推导 + 编译器自动内存管理（含 phpc 安全边界）

## 一、设计总纲

两条原则：

1. **PHP 类型默认自动推导**——局部变量不写类型，首次赋值定死（旧版 tphp 语义）；
   **C 类型必须显式声明**——`c.*` / cstruct / 指针类型不允许推断，C 侧值必须落进显式声明的变量；
2. **内存安全由编译器代码追踪自动处理**——引用计数插桩（uniform ownership），开发者零手动、
   零感知；phpc 是唯一显式 unsafe 边界（类比 Rust unsafe），其余代码构造即安全。

```
┌─ 安全核心：纯 TinyPHP 代码 ─────────────────────────────┐
│ · 类型自动推导，开发者不写多余类型                        │
│ · 数组/对象/接口：编译器 RC 插桩自动回收（确定性）         │
│ · string 池 + 进程回收；cstruct 值语义；变量零初始化       │
│ · 越界/空指针 → panic（已有）；无手动 free               │
│ · 已知例外：引用循环泄漏（文档化，--mem-stats 可检测）     │
├─ phpc = 显式 unsafe 边界 ──────────────────────────────┤
│ · C 符号调用类型信任程序员；C 内存手动所有（c->free）      │
│ · 安全默认：php_str 深拷贝；php_str_ref 借用需显式        │
└────────────────────────────────────────────────────────┘
```

## 二、类型自动推导

- **局部变量**：`$x = 5;` → int；`$s = "a";` → string；`$a = [1, 2, 3];` → array<int>；
  `$p = new Point();` → Point。首次赋值定死，后续赋值必须可赋值（`$x = "a";` 编译错）——旧版语义
- **显式声明依旧合法且必要**：`int $x = 5;` / `array<int> $a = [];`（空数组必须显式）/
  `c.i32 $n = c->foo();`（**C 类型禁止推断**：`$v = c->foo();` 编译错，提示 C 类型须显式声明）
- **参数 / 返回值 / 类属性**：保持显式必填（与 PHP 一致）
- foreach 循环变量保持自动（由数组元素类型推出）
- 实现：Parser 无需改动（`$x = expr` 已是 AssignExpr）；Checker 的赋值目标查找改为
  "未定义 → 推断注册（禁 C 侧类型）"，显式 LocalDecl 保持不变

## 三、内存安全：编译器代码追踪（RC 插桩，12 条 uniform 规则）

核心思想：**堆值表达式求值即持有（owned），语句结束释放未转移的持有，作用域退出释放堆局部**。
类型编译期精确已知，插桩无歧义，开发者零感知。

| # | 位置 | 规则 |
| - | ---- | ---- |
| R1 | 变量读（堆类型） | incref（owned 引用），语句尾对应 decref |
| R2 | new / c-> 调用返回指针 | 天然 owned |
| R3 | TinyPHP 函数调用 | 返回值 owned |
| R4 | 赋值 `a = expr` | eval→owned temp；decref 旧 a；a = temp |
| R5 | 字段写 `o->f = expr` | 同 R4 + self-assign guard |
| R6 | 容器写 push/set | runtime 钩子：incref 新元素、decref 旧/被删（elem_flags 递归） |
| R7 | 传参 | args→owned temps；callee 入口 incref 堆形参、出口 decref |
| R8 | return | eval→owned temp；decref 全部作用域堆局部；return temp |
| R9 | 语句结束 | 释放该语句全部未转移 owned temps |
| R10 | 块退出 | decref 本块堆局部（声明逆序） |
| R11 | break/continue | 先 decref 当前块到循环体的堆局部再跳转 |
| R12 | 全局/静态变量 | immortal（持有不释放） |

- string 不插桩（池）；cstruct 不插桩（值类型）；phpc 指针不插桩（C 所有）
- 与旧版的差异：旧版“赋值=move+函数尾集中清理”存在双释放洞；我们用精确 RC（Swift 式
  uniform），规则无例外、mem-stats 可验证；与 V 的差异：V 无 RC 走“赋值即克隆”+GC 兜底，
  我们零拷贝共享且无 GC 依赖。last-use move 优化留后续版本
- **可验证性**：`--mem-stats` 运行时统计数组/对象分配与释放计数，退出打印 `leaks=N`；
  tests/run.php 对全部用例断言 `leaks=0`——RC 正确性进入自动化验证

## 四、phpc（已确认口径 + 内存安全边界）

- `#include <...>"..."`、`#flag -l...`（.c 自动加入编译）、`#struct`（仅登记布局，头文件提供类型）
- `c->符号(...)` 直连调用（参数不做隐式转换：数值本就是 C 类型直传，string 需 `c_str()`，
  cstruct 按值）；`c->NULL`/`c->SEEK_END` 常量引用（CVAL）
- 返回值 CVAL：可赋 c.*/指针/cstruct/数值/bool（条件允许），不可赋 string（用 php_str）
- **内存边界规则**：
  - `c_str($s)` string→char*：借用池内存（池与进程同寿，**安全**）
  - `php_str($p)` char*→string：**深拷贝**（安全默认，C 侧 free/修改不影响）
  - `php_str_ref($p)`：零拷贝借用（仅生命周期确定的静态数据，文档注明悬垂风险）
  - `c->malloc` 等返回指针：C 所有、手动 `c->free`（unsafe，文档注明）
  - 传给 C 的借用仅调用期间有效

## 五、实现落点

| 组件 | 改动 |
| ---- | ---- |
| Checker | ① 未定义变量赋值 = 推断声明（禁 C 侧类型推断）② RC 插桩所需的全部类型信息（已有） |
| Gen（核心） | ① 作用域栈（每层 owned 堆变量）② 语句级簿记：堆变量读 incref 前置 + 语句尾释放 ③ 含堆产生式的 RHS 语句表达式捕获 ④ 赋值/字段写 decref 旧值 + self-assign guard ⑤ 块尾/return/break/continue 清理 ⑥ 传参/返回 |
| runtime | ① mem-stats 计数器 + atexit 报告 ② 数组元素钩子（elem_flags 递归 incref/decref）③ `tphp_php_str()`（深拷贝）/`tphp_php_str_ref()`（零拷贝，phpc 预置） |
| Scanner/Parser/Ast | phpc：#include/#flag/#struct 指令、c-> 直连、cstr/php_str/php_str_ref 内置、#struct 体解析 |
| Builder/CLI | `--mem-stats` 透传；#flag 参数追加 C 编译命令 |
| run.php | 全部用例统一 --mem-stats + 断言 leaks=0 |
| 文档 | `doc/memory.md`（内存安全模型全文）、`doc/phpc.md`（phpc 规范）、README/grammar.md 类型推导与内存章节 |

## 六、实施顺序与验收

1. 类型自动推导（Checker）+ 用例更新（现有用例可保持显式写法，回归不受影响）
2. runtime：mem-stats 计数器 + 元素钩子 + php_str
3. Gen RC 插桩（核心）：作用域栈 → 语句簿记 → 赋值/字段 → 控制流清理，逐场景调平
4. `--mem-stats` + `tests/cases/11_memory.php`（共享/重赋值/容器嵌套/传参/返回/foreach/循环分配），run.php 全用例 leaks=0 断言
5. phpc：# 指令 + c-> + c_str/php_str/php_str_ref + cstruct + `tests/cases/12_phpuc.php`（自写微型 C 库 phpuc_demo.h/.c）
6. 文档：doc/memory.md、doc/phpc.md、README/grammar.md
7. 全量回归：run.php（14 用例）+ cross.php + examples + 语法检查

预计改动：Checker ~80 行，Gen ~350 行（核心），runtime ~80 行，Scanner/Parser/Ast ~200 行（phpc），Builder/CLI ~40 行，测试 2 个新用例，文档 2 份新增 + 2 份更新。