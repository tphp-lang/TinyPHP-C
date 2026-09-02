<?php

// Main 构造器收命令行参数（旧版 tphp 惯例）：测试架直接运行 exe，argc=1
// expect:
// argc=1
// argv0-len>0
// true

class Main
{
    public function __construct(int $argc, array<string> $argv)
    {
        echo "argc=", $argc, "\n";
        if (len($argv) == $argc) {
            echo "argv0-len>0", "\n";
        }
        echo $argc >= 1 && len($argv[0]) > 0, "\n";
    }

    public function main(): void
    {
    }
}
