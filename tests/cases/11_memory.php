<?php

// expect:
// 3
// 10
// 6 6
// 110
// total=18
// 4 4 4 4 4 4 4 4 4 4
// final 12

class Buffer
{
    public array<int> $data;
    public string $tag;
    public function __construct(string $tag, int $size)
    {
        $this->tag = $tag;
        $this->data = [];
        for (int $i = 0; $i < $size; $i++) {
            $this->data[] = $i * 2;
        }
    }
    public function sum(): int
    {
        int $t = 0;
        foreach ($this->data as $v) {
            $t += $v;
        }
        return $t;
    }
}

function buildArray(int $n): array<int>
{
    array<int> $a = [];
    for (int $i = 0; $i < $n; $i++) {
        $a[] = $i;
    }
    return $a;
}

function sumArray(array<int> $a): int
{
    int $s = 0;
    foreach ($a as $v) {
        $s += $v;
    }
    return $s;
}

function buildBuffer(string $tag): Buffer
{
    return new Buffer($tag, 4);
}

class Main
{
    public function main(): void
    {
        // 推断声明 + 重赋值（旧引用释放）
        $x = buildArray(3);
        echo sumArray($x), "\n";          // 0+1+2 = 3
        $x = buildArray(5);
        echo sumArray($x), "\n";          // 0+..+4 = 10

        // 变量间共享：两个名字各持一份引用
        $y = $x;
        $y[] = 100;
        echo len($x), " ", len($y), "\n"; // 5 6（引用语义：数组共享? 值拷贝?）
        echo sumArray($x), "\n";

        // 容器嵌套：对象持有数组，数组持有对象
        array<Buffer> $bufs = [];
        for (int $i = 0; $i < 3; $i++) {
            $bufs[] = new Buffer("b" . (string)$i, 3);
        }
        int $total = 0;
        foreach ($bufs as $b) {
            $total += $b->sum();
        }
        echo "total=", $total, "\n";      // 3 * (0+2+4) = 18

        // 循环内分配（每轮新建/丢弃）
        for (int $r = 0; $r < 10; $r++) {
            $tmp = buildBuffer("t");
            echo len($tmp->data), " ";
        }
        echo "\n";

        // 传参/返回共享
        $b = buildBuffer("final");
        echo $b->tag, " ", $b->sum(), "\n";
    }
}
