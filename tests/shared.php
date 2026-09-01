<?php

declare(strict_types=1);

/**
 * 库模式测试：--shared 产物 + #[export("c_add")] 自定义符号导出。
 *
 *   1) --shared 编译 tests/shared/demo.php → 动态库（本机 .dll / linux .so）
 *   2) Windows：TCC 编译 host.c（LoadLibrary），加载动态库按 c_add 调用，
 *      并校验默认符号 tphp_add 已被 #[export] 重命名而消失
 *   3) Linux 目标：校验 ELF 魔数与导出符号名（静态检查）
 *
 * 用法：php tests/shared.php
 */

$root = dirname(__DIR__);
$php = PHP_BINARY;
$pass = 0;
$fail = 0;

$outDir = $root . '/build/tests/shared';
if (!is_dir($outDir)) {
    mkdir($outDir, 0777, true);
}

$demo = $root . '/tests/shared/demo.php';

function check(string $name, bool $ok, string $detail = ''): bool
{
    global $pass, $fail;
    if ($ok) {
        echo "PASS {$name}\n";
        $pass++;
    } else {
        echo "FAIL {$name}\n{$detail}\n";
        $fail++;
    }
    return $ok;
}

// ---- 1) 本机 --shared 编译 ----
$dll = $outDir . '/' . (PHP_OS_FAMILY === 'Windows' ? 'demo.dll' : 'demo.so');
@unlink($dll);
$build = shell_exec(escapeshellarg($php) . ' ' . escapeshellarg($root . '/main.php')
    . ' ' . escapeshellarg($demo) . ' --shared -o ' . escapeshellarg($dll) . ' 2>&1');
if (!check('shared 编译（本机动态库）', is_file($dll), (string) $build)) {
    exit(1);
}

// ---- 2) 宿主程序真实调用（仅 Windows 本机）----
if (PHP_OS_FAMILY === 'Windows') {
    $hostExe = $outDir . '/host.exe';
    @unlink($hostExe);
    $tcc = $root . '/tcc/tcc.exe';
    shell_exec(escapeshellarg($tcc) . ' -o ' . escapeshellarg($hostExe) . ' '
        . escapeshellarg($root . '/tests/shared/host.c') . ' -lkernel32 2>&1');
    $run = shell_exec(escapeshellarg($hostExe) . ' ' . escapeshellarg($dll) . ' 2>&1') ?? '';
    $lines = array_values(array_filter(array_map('trim', explode("\n", str_replace("\r", "\n", $run))), 'strlen'));
    check('宿主按 #[export] 符号调用（c_add(20,22) → 42）', ($lines[0] ?? '') === '42', $run);
    check('默认符号 tphp_add 已被重命名消除', ($lines[1] ?? '') === 'no-tphp_add', $run);
}

// ---- 3) Linux 目标：ELF 产物检查 ----
// 注：自带 musl 交叉 tcc 的 -shared 产物不含动态符号表（doc/phpc.md 已注明），
// 符号级验证仅在 Windows 宿主可行，故此处只校验 ELF 魔数。
$so = $outDir . '/demo.so';
@unlink($so);
$build = shell_exec(escapeshellarg($php) . ' ' . escapeshellarg($root . '/main.php')
    . ' ' . escapeshellarg($demo) . ' --shared -os linux -arch x86_64 -o ' . escapeshellarg($so) . ' 2>&1');
check('linux .so 为 ELF 产物', is_file($so) && str_starts_with((string) file_get_contents($so), "\x7fELF"), (string) $build);

echo "\n{$pass} 通过, {$fail} 失败\n";
exit($fail === 0 ? 0 : 1);
