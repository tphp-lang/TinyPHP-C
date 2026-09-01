<?php

declare(strict_types=1);

/*
 * TinyPHP — 强类型 PHP 子集 → C 转译器。
 *
 * CLI 入口：解析参数后交给 Builder 串联完整流水线。
 * 自带 PSR-4 自动加载兜底，未执行 composer install 也可直接运行。
 */

spl_autoload_register(static function (string $class): void {
    if (!str_starts_with($class, 'Tphp\\')) {
        return;
    }
    $path = __DIR__ . '/src/' . str_replace('\\', '/', substr($class, 5)) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

if (is_file(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
}

use Tphp\Pref\Pref;

$pref = Pref::parse($argv);

if ($pref->errors !== []) {
    foreach ($pref->errors as $error) {
        fwrite(STDERR, "TinyPHP: {$error}\n");
    }
    fwrite(STDERR, Pref::usage());
    exit(2);
}

if ($pref->help) {
    echo Pref::usage();
    exit(0);
}

if ($pref->version) {
    echo "TinyPHP 0.3.0\n";
    exit(0);
}

exit((new Tphp\Builder\Builder($pref))->run());
