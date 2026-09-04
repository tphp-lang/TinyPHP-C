<?php

declare(strict_types=1);

/*
 * TinyPHP — 强类型 PHP 子集 → C 转译器。
 *
 * CLI 入口：解析参数后交给 Builder 串联完整流水线。
 * 自带 PSR-4 自动加载兜底，未执行 composer install 也可直接运行。
 */

/**
 * CLI 输出 RGB 真彩色文本（返回字符串）
 * @param string $str 要着色的文本
 * @param int $r 红色通道 0-255
 * @param int $g 绿色通道 0-255
 * @param int $b 蓝色通道 0-255
 * @return string 着色后的字符串，非终端/非CLI环境自动返回纯文本
 */
function echoRgb(string $str, int $r, int $g, int $b): string
{
    // 1. 非 CLI 环境直接返回纯文本（避免 web 场景输出乱码）
    if (PHP_SAPI !== 'cli') {
        return $str;
    }

    // 2. RGB 值钳位到 0-255 合法范围，防止越界
    $r = max(0, min(255, $r));
    $g = max(0, min(255, $g));
    $b = max(0, min(255, $b));

    // 3. 非终端输出（重定向到文件/管道）自动去除颜色码
    // stream_isatty 是 PHP 7.2+ 内置跨平台函数，兼容 Windows
    if (!stream_isatty(STDOUT)) {
        return $str;
    }

    // 4. 拼接 ANSI 24位真彩色转义序列 + 文本 + 重置格式
    // 格式：\033[38;2;R;G;Bm 文本 \033[0m
    return "\033[38;2;{$r};{$g};{$b}m{$str}\033[0m";
}

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
