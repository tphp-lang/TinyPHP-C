<?php

declare(strict_types=1);

namespace Tphp\Pref;

/**
 * 编译选项。
 *
 * 只负责解析与承载 CLI 标志，不做任何 I/O。
 */
final class Pref
{
    /** 支持的目标平台（自带 TCC 交叉编译器覆盖这些组合）。 */
    public const TARGETS = [
        'windows' => ['x86_64', 'i386'],
        'linux' => ['x86_64', 'arm64'],
    ];

    /**
     * @param list<string> $inputs 输入 .php 文件，第一个为入口
     * @param list<string> $cflags 透传给 C 编译器的额外参数
     * @param list<string> $errors 参数用法错误
     */
    private function __construct(
        public readonly array $inputs,
        public readonly ?string $output,
        public readonly bool $emitC,
        public readonly bool $run,
        public readonly string $cc,
        public readonly string $os,
        public readonly string $arch,
        public readonly bool $noMain,
        public readonly bool $shared,
        public readonly array $cflags,
        public readonly bool $memStats,
        public readonly bool $help,
        public readonly bool $version,
        public readonly array $errors,
    ) {}

    public static function parse(array $argv): self
    {
        $inputs = [];
        $output = null;
        $emitC = false;
        $run = false;
        $memStats = false;
        $cc = 'tcc';
        $noMain = false;
        $shared = false;
        $cflags = [];
        $help = false;
        $version = false;
        $errors = [];
        $explicitOs = null;
        $explicitArch = null;

        $args = array_slice($argv, 1);
        $count = count($args);
        for ($i = 0; $i < $count; $i++) {
            $arg = $args[$i];
            if ($arg === '-h' || $arg === '--help') {
                $help = true;
            } elseif ($arg === '-v' || $arg === '--version') {
                $version = true;
            } elseif ($arg === '--emit-c') {
                $emitC = true;
            } elseif ($arg === '--run') {
                $run = true;
            } elseif ($arg === '--mem-stats') {
                $memStats = true;
            } elseif ($arg === '--no-main') {
                $noMain = true;
            } elseif ($arg === '--shared') {
                $shared = true;
                $noMain = true; // 库模式不需要入口
            } elseif ($arg === '-o') {
                if ($i + 1 >= $count) {
                    $errors[] = '-o 需要一个参数：-o <输出路径>';
                    break;
                }
                $output = $args[++$i];
            } elseif ($arg === '-os' || $arg === '-arch') {
                if ($i + 1 >= $count) {
                    $errors[] = "{$arg} 需要一个参数";
                    break;
                }
                if ($arg === '-os') {
                    $explicitOs = $args[++$i];
                } else {
                    $explicitArch = $args[++$i];
                }
            } elseif (str_starts_with($arg, '-o')) {
                $output = substr($arg, 2);
            } elseif (str_starts_with($arg, '--cc=')) {
                $cc = substr($arg, 5);
            } elseif (str_starts_with($arg, '--cflag=')) {
                $cflags[] = substr($arg, 8);
            } elseif (str_starts_with($arg, '-')) {
                $errors[] = "未知选项：{$arg}";
            } else {
                $inputs[] = $arg;
            }
        }

        if (!$help && !$version && $inputs === []) {
            $errors[] = '缺少输入文件';
        }

        if (!in_array($cc, ['tcc', 'gcc', 'clang'], true)) {
            $errors[] = "不支持的 C 编译器：{$cc}（可选 tcc / gcc / clang）";
        }

        // 目标解析：默认本机系统 + 本机架构；只指定 -os 时架构默认 x86_64
        [$os, $arch] = self::resolveTarget($explicitOs, $explicitArch);

        if (!isset(self::TARGETS[$os])) {
            $errors[] = '不支持的目标系统：' . $os . '（可选 ' . implode(' / ', array_keys(self::TARGETS)) . '）';
        } elseif (!in_array($arch, self::TARGETS[$os], true)) {
            $errors[] = "目标 {$os} 不支持体系结构 {$arch}（可选 " . implode(' / ', self::TARGETS[$os]) . '）';
        }

        return new self($inputs, $output, $emitC, $run, $cc, $os, $arch, $noMain, $shared, $cflags, $memStats, $help, $version, $errors);
    }

    /** @return array{string, string} */
    private static function resolveTarget(?string $explicitOs, ?string $explicitArch): array
    {
        $hostOs = strtolower(PHP_OS_FAMILY);
        $hostArch = self::normalizeArch(php_uname('m'));

        if ($explicitOs !== null) {
            $os = $explicitOs;
            // 交叉编译默认 x86_64；显式给了 -arch 则用之
            $arch = $explicitArch ?? 'x86_64';
        } else {
            $os = isset(self::TARGETS[$hostOs]) ? $hostOs : 'windows';
            $arch = $explicitArch
                ?? ($hostArch !== '' && in_array($hostArch, self::TARGETS[$os] ?? [], true) ? $hostArch : 'x86_64');
        }
        return [$os, $arch];
    }

    /** 机器名 → 规范架构名。 */
    public static function normalizeArch(string $machine): string
    {
        $m = strtolower($machine);
        return match (true) {
            in_array($m, ['x86_64', 'amd64', 'x64'], true) => 'x86_64',
            in_array($m, ['arm64', 'aarch64'], true) => 'arm64',
            in_array($m, ['i386', 'i486', 'i586', 'i686', 'x86'], true) => 'i386',
            default => '',
        };
    }

    public static function usage(): string
    {
        return <<<TXT
        用法: php main.php <file.php> [file2.php ...] [选项]

        选项:
          -o <path>     输出路径（默认当前目录 <入口文件名>[.exe]
          --emit-c      只生成 C 源码，不调用 C 编译器
          --run         编译后立即运行生成的可执行文件（仅本机目标）
          --mem-stats   退出时打印内存分配/释放统计（leaks=N）
          --cc=<name>   C 编译器：tcc（默认）/ gcc / clang
          -os <name>    目标系统：windows（默认）/ linux
          -arch <name>  目标架构：x86_64（默认）/ i386(windows) / arm64(linux)
          --no-main     不生成 main()（库模式，供固件/宿主程序调用）
          --shared      编译为动态库（.dll/.so），隐含 --no-main
          --cflag=<arg> 透传参数给 C 编译器（可多次使用）
          -h, --help    显示帮助
          -v, --version 显示版本

        交叉编译（利用自带 TCC）：
          php main.php main.php -os linux -arch arm64   # → Linux arm64 静态 ELF（musl）
          php main.php main.php -os linux -arch x86_64  # → Linux x86_64 静态 ELF
          php main.php main.php -os windows -arch i386  # → 32 位 Windows exe

        TXT;
    }
}
