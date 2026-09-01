<?php

declare(strict_types=1);

/**
 * 交叉编译测试：用自带 TCC 把 hello 用例编译到各目标，校验产物魔数。
 *
 * 用法：php tests/cross.php
 */

$root = dirname(__DIR__);
$php = PHP_BINARY;

/** @var array<string, array{os: string, arch: string, expect: string, magic: string}> */
$targets = [
    'windows-x86_64(host)' => ['os' => 'windows', 'arch' => 'x86_64', 'expect' => 'cross/host.exe', 'magic' => '4d5a'],
    'windows-i386' => ['os' => 'windows', 'arch' => 'i386', 'expect' => 'cross/hello_i386.exe', 'magic' => '4d5a'],
    'linux-x86_64' => ['os' => 'linux', 'arch' => 'x86_64', 'expect' => 'cross/hello_x64', 'magic' => '7f454c46'],
    'linux-arm64' => ['os' => 'linux', 'arch' => 'arm64', 'expect' => 'cross/hello_arm64', 'magic' => '7f454c46'],
];

$pass = 0;
$fail = 0;
$build = $root . '/build/cross';
if (!is_dir($build)) {
    mkdir($build, 0777, true);
}

foreach ($targets as $name => $t) {
    $out = $root . '/build/' . $t['expect'];
    $cmd = escapeshellarg($php) . ' ' . escapeshellarg($root . '/main.php')
        . ' ' . escapeshellarg($root . '/tests/cases/01_arith.php')
        . ' -os ' . $t['os'] . ' -arch ' . $t['arch']
        . ' -o ' . escapeshellarg($out) . ' 2>&1';
    shell_exec($cmd);

    if (!is_file($out)) {
        echo "FAIL {$name}（编译失败）\n";
        $fail++;
        continue;
    }
    $magic = bin2hex((string) file_get_contents($out, false, null, 0, 4));
    if (!str_starts_with($magic, $t['magic'])) {
        echo "FAIL {$name}（魔数不匹配：{$magic}）\n";
        $fail++;
        continue;
    }
    echo "PASS {$name}\n";
    $pass++;
}

// --no-main：生成代码中不应有 main()
shell_exec(escapeshellarg($php) . ' ' . escapeshellarg($root . '/main.php')
    . ' ' . escapeshellarg($root . '/tests/cases/01_arith.php')
    . ' --no-main -o ' . escapeshellarg($root . '/build/cross/nomain.exe') . ' 2>&1');
$c = (string) file_get_contents($root . '/build/cross/nomain.c');
if (str_contains($c, 'int main(')) {
    echo "FAIL no-main（生成代码包含 main）\n";
    $fail++;
} else {
    echo "PASS no-main\n";
    $pass++;
}

echo "\n{$pass} 通过, {$fail} 失败\n";
exit($fail === 0 ? 0 : 1);
