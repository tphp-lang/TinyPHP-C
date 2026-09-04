<?php

declare(strict_types=1);

/**
 * `.` 指令测试：递归收集当前目录全部 *.php（含子目录），
 * 跳过 vendor / build / .git 与隐藏目录，入口按 class Main 判定（产物以入口命名）。
 *
 *   1) 建临时目录树：main.php（入口）+ lib.php + sub/helper.php + ignored/vendor/x.php
 *   2) 在该目录内 `php main.php run .`
 *   3) 断言：编译成功、输出含子目录文件内容、产物命名取入口、vendor 被跳过
 *
 * 用法：php tests/dot.php
 */

$root = dirname(__DIR__);
$php = PHP_BINARY;
$pass = 0;
$fail = 0;

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

$dir = $root . '/build/tests/dot_src';
$exe = $dir . '/main.exe';
$cFile = $dir . '/build/main.c';
@mkdir($dir . '/sub', 0777, true);
@mkdir($dir . '/ignored/vendor', 0777, true);
@mkdir($dir . '/build', 0777, true);

file_put_contents($dir . '/main.php', <<<'PHP'
<?php

class Main
{
    public function main(): void
    {
        echo "main-ok\n";
        echo greet(), "\n";
        echo sub_helper(), "\n";
    }
}
PHP);

file_put_contents($dir . '/lib.php', <<<'PHP'
<?php

function greet(): string
{
    return "lib-ok";
}
PHP);

file_put_contents($dir . '/sub/helper.php', <<<'PHP'
<?php

function sub_helper(): string
{
    return "sub-ok";
}
PHP);

// vendor 目录必须被跳过（其中的 PHP 文件不参与编译）
file_put_contents($dir . '/ignored/vendor/x.php', <<<'PHP'
<?php

function this_should_not_compile(): void
{
}
PHP);

@unlink($exe);
@unlink($cFile);
$cmd = 'cd ' . escapeshellarg($dir) . ' && ' . escapeshellarg($php) . ' '
    . escapeshellarg($root . '/main.php') . ' run . 2>&1';
$out = shell_exec($cmd) ?? '';

check('递归编译成功（含子目录）', is_file($exe), $out);
check('产物以入口命名（main.exe，与参数顺序无关）', is_file($exe), $out);
check('入口输出', str_contains($out, 'main-ok'), $out);
check('根层文件包含', str_contains($out, 'lib-ok'), $out);
check('子目录文件包含', str_contains($out, 'sub-ok'), $out);

$c = is_file($cFile) ? (string) file_get_contents($cFile) : '';
check('C 源码生成于 build/（相对 cwd）', $c !== '', (string) $cFile);
check('vendor 目录被跳过', $c !== '' && !str_contains($c, 'this_should_not_compile'), 'C 源码含 vendor 内容');

// 清理
array_map('unlink', array_merge(
    glob($dir . '/*.php') ?: [],
    glob($dir . '/sub/*.php') ?: [],
    glob($dir . '/ignored/vendor/*.php') ?: [],
    [$exe, $cFile],
));
@rmdir($dir . '/sub');
@rmdir($dir . '/ignored/vendor');
@rmdir($dir . '/ignored');
@rmdir($dir . '/build');
@rmdir($dir);

echo "\n{$pass} 通过, {$fail} 失败\n";
exit($fail === 0 ? 0 : 1);
