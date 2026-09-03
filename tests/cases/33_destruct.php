<?php

// __destruct：用户析构先于字段释放（对象引用归零时触发；main 作用域退出时执行）
// expect:
// open
// use
// (freed)
// close: buf

class Res
{
    public string $buf;

    public function __construct(string $buf)
    {
        $this->buf = $buf;
        echo "open", "\n";
    }

    public function __destruct(): void
    {
        echo "close: ", $this->buf, "\n";
    }
}

class Main
{
    public function main(): void
    {
        Res $r = new Res("buf");
        echo "use", "\n";
        echo "(freed)", "\n";
    }
}
