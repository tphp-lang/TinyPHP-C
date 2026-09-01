<?php

declare(strict_types=1);

namespace Tphp\Builder;

use Tphp\Pref\Pref;

/**
 * 调用 C 编译器把生成的 .c 变成产物。
 *
 * 默认用项目自带的 TCC：
 *   - windows x86_64（本机）  → tcc/tcc.exe
 *   - windows i386            → tcc/i386-win32-tcc.exe   （32 位 PE）
 *   - linux x86_64            → tcc/x86_64-tcc.exe       （musl 静态 ELF）
 *   - linux arm64             → tcc/arm64-tcc.exe        （musl 静态 ELF）
 * Linux 交叉产物静态链接 musl，不依赖目标机 libc，可直接放到目标板运行；
 * 也可以 --cc gcc/clang 并用 --cflag 透传交叉参数。
 */
final class Cc
{
    public static function compile(Pref $pref, string $cFile, string $exeFile, string $runtimeDir, array $cflags = [], array $extraSources = []): ?string
    {
        $cmd = self::buildCommand($pref, $cFile, $exeFile, $runtimeDir, $cflags, $extraSources);
        $escaped = implode(' ', array_map('escapeshellarg', $cmd));
        echo "> {$escaped}\n";

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $proc = proc_open($escaped, $descriptors, $pipes);
        if (!is_resource($proc)) {
            fwrite(STDERR, "TinyPHP: 无法启动 C 编译器\n");
            return null;
        }
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $code = proc_close($proc);

        if ($stdout !== '' && $stdout !== false) {
            echo $stdout;
        }
        if ($stderr !== '' && $stderr !== false) {
            fwrite(STDERR, $stderr);
        }
        if ($code !== 0) {
            fwrite(STDERR, "TinyPHP: C 编译失败（退出码 {$code}）\n");
            return null;
        }
        return $exeFile;
    }

    /** @return list<string> */
    private static function buildCommand(Pref $pref, string $cFile, string $exeFile, string $runtimeDir, array $cflags = [], array $extraSources = []): array
    {
        $cmd = [$pref->cc === 'tcc' ? self::tccBinary($pref) : $pref->cc];
        $cmd[] = '-o';
        $cmd[] = $exeFile;
        $cmd[] = '-I';
        $cmd[] = $runtimeDir;
        if ($pref->shared) {
            $cmd[] = '-shared';
            // TCC 的 PE 产物默认不导出普通符号，需显式开启（Linux .so 限制见 doc/phpc.md）
            if ($pref->os === 'windows') {
                $cmd[] = '-Wl,--export-all-symbols';
            }
        }
        // #flag 参数（用户 --cflag 之后追加 .c 附加源文件；
        // .c 项从 flag 中剔除——已提升为 extraSources，避免重复编译）
        foreach (array_merge($cflags, $pref->cflags) as $flag) {
            if (str_ends_with($flag, '.c')) {
                continue;
            }
            $cmd[] = $flag;
        }
        foreach ($extraSources as $src) {
            $cmd[] = $src;
        }
        $cmd[] = $cFile;
        return $cmd;
    }

    /** 按目标平台选择自带 TCC 二进制。 */
    private static function tccBinary(Pref $pref): string
    {
        $root = dirname(__DIR__, 2);
        if ($pref->os === 'linux') {
            return $pref->arch === 'arm64'
                ? $root . '/tcc/arm64-tcc.exe'
                : $root . '/tcc/x86_64-tcc.exe';
        }
        if ($pref->arch === 'i386') {
            return $root . '/tcc/i386-win32-tcc.exe';
        }
        // 本机目标：找不到内置 tcc 时退回 PATH
        $exe = PHP_OS_FAMILY === 'Windows' ? 'tcc.exe' : 'tcc';
        $local = $root . '/tcc/' . $exe;
        return is_file($local) ? $local : 'tcc';
    }
}
