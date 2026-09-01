<?php

// 平台条件编译：#if / #elif / #else / #endif（按 -os / -arch / --cc 求值，非命中分支整段丢弃）
// expect:
// windows-x64
// tag=x64
// on-windows

#if windows
const string PLATFORM = "windows";
#elif linux
const string PLATFORM = "linux";
#else
const string PLATFORM = "other";
#endif

#if x86_64
const string ARCH = "x64";
#else
const string ARCH = "other-arch";
#endif

#if arm64
function platTag(): string
{
    return "aarch64";
}
#else
function platTag(): string
{
    return "x64";
}
#endif

class Main
{
    public function main(): void
    {
        echo PLATFORM, "-", ARCH, "\n";
        echo "tag=", platTag(), "\n";
        // 函数体内同样可用（允许缩进）
        #if windows
        echo "on-windows\n";
        #endif
        #if !windows
        echo "not-windows\n";
        #endif
    }
}
