# 编译器

```sh
  _____ _             ____  _   _ ____  
 |_   _(_)_ __  _   _|  _ \| | | |  _ \ 
  | | | | '_ \| | |/ _ \| | | | |_) |
  | | | | | | | |_| | |_| | |_| |  __/ 
  |_| |_|_| |_|\__, |_|   |_| |_|_|    
                |___/                   

用法: php main.php [命令] [file.php ... | .] [指令] 

命令:
  build         编译为可执行文件（默认）
  run           编译后立即运行生成的可执行文件（仅本机目标）
  shared        编译为动态库（.dll/.so），隐含 --no-main

指令:
  -o <path>     输出路径（默认当前目录 <入口文件名>[.exe]；C 源码默认输出到 build/，-o 时与产物同目录）
  --emit-c      只生成 C 源码，不调用 C 编译器
  --mem-stats   退出时打印内存分配/释放统计（leaks=N）
  --cc <name>   C 编译器：tcc（默认）/ gcc / clang
  -os <name>    目标系统：windows（默认）/ linux
  -arch <name>  目标架构：x86_64（默认）/ i386(windows) / arm64(linux)
  --no-main     不生成 main()（库模式，供固件/宿主程序调用）
  --cflag <arg> 透传参数给 C 编译器（可多次使用）

帮助:
  -h, --help    显示帮助
  -v, --version 显示版本
```

> - 文件必须是 `*.php` 文件，可选择多个文件或者 `.` 表示当前目录**递归**的全部 `*.php` 文件
> - 递归跳过 `vendor/`、`build/`、`.git/` 与任何以 `.` 开头的目录（隐藏目录）
> - 命令可省略（默认 `build`）；可出现在任意位置，非 `*.php` 的 `build`/`run`/`shared`
>   词即命令（文件名与命令因此天然消歧）
> - 入口 = 含全局 `class Main` 的文件（产物命名取入口文件，与参数顺序无关）

## 命令

```sh
php main.php build index.php            # 单文件（build 为默认，可省略）
php main.php run index.php              # 编译并立即运行
php main.php shared index.php           # 编译为动态库（.dll/.so）

php main.php build index.php config.php ...   # 多文件（其余 *.php 免 import 直接可见）
php main.php run .                      # 当前目录的全部 *.php 文件
```

## 指令

```sh
php main.php build index.php -o out.exe     # 输出路径（C 源码与产物同目录）
php main.php build . --emit-c               # 只生成 C 源码（build/ 目录）
php main.php build main.php -os linux -arch arm64   # → Linux arm64 静态 ELF（musl）
php main.php shared main.php --cc gcc --cflag -mcpu=cortex-m4   # gcc/clang 透传参数
```
