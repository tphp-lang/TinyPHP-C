<?php

// expect:
// == arithmetic ==
// 7
// 9
// 3
// 1
// 1024
// -4
// 3.75
// 40 15
// 1 7 6 -6
// 17
// abc
// == control ==
// while 0
// while 1
// while 2
// do-once
// for 0
// for 2
// two
// good
// == fn ==
// 55
// 5050
// == array ==
// 4
// 0:11 1:20 2:30 3:40
// [php][c]
// 70
// 3
// == string ==
// hello world, 1+2=17!
// 5
// true
// true
// e
// tinyphp
// == class ==
// generic makes a sound
// rex: woof!
// rex: woof!
// rex fetches the ball
// rex has 4 legs
// true
// == static ==
// 2
// 2
// == cast ==
// 124
// 6.28
// 42!
// true
// int(17)
// string(2) "hi"
// array(2) ["php", "c"]
// true
// true
// 1

function fib(int $n): int
{
    if ($n < 2) {
        return $n;
    }
    return fib($n - 1) + fib($n - 2);
}

function sumTo(int $n): int
{
    int $s = 0;
    for (int $i = 1; $i <= $n; $i++) {
        $s += $i;
    }
    return $s;
}

class Animal
{
    public string $name;
    protected int $legs = 4;
    public function __construct(string $name)
    {
        $this->name = $name;
    }
    public function speak(): void
    {
        echo $this->name, " makes a sound\n";
    }
    public function describe(): string
    {
        return $this->name . " has " . (string)$this->legs . " legs";
    }
}

class Dog extends Animal
{
    public function speak(): void
    {
        echo $this->name, ": woof!\n";
    }
    public function fetch(): string
    {
        return $this->name . " fetches the ball";
    }
}

class Counter
{
    public static int $count = 0;
    public int $id;
    public function __construct()
    {
        Counter::$count += 1;
        $this->id = Counter::$count;
    }
}

class Main
{
    public function main(): void
    {
        // 算术与优先级
        echo "== arithmetic ==\n";
        echo 1 + 2 * 3, "\n";            // 7
        echo (1 + 2) * 3, "\n";          // 9
        echo 7 / 2, "\n";                // 3 (C 整除)
        echo 7 % 3, "\n";                // 1
        echo 2 ** 10, "\n";              // 1024
        echo -2 ** 2, "\n";              // -4 (** 优先于一元减)
        double $f = 1.5 + 2.25;
        echo $f, "\n";                   // 3.75
        echo 10 << 2, " ", 255 >> 4, "\n"; // 40 15
        echo 5 & 3, " ", 5 | 3, " ", 5 ^ 3, " ", ~5, "\n"; // 1 7 6 -6

        // 自增自减 / 复合赋值
        int $x = 5;
        $x++;
        ++$x;
        $x += 10;
        echo $x, "\n";                   // 17
        string $s = "a";
        $s .= "bc";
        echo $s, "\n";                   // abc

        // 控制流
        echo "== control ==\n";
        int $i = 0;
        while ($i < 3) {
            echo "while ", $i, "\n";
            $i++;
        }
        do {
            echo "do-once\n";
        } while (false);
        for (int $j = 0; $j < 3; $j++) {
            if ($j == 1) {
                continue;
            }
            echo "for ", $j, "\n";
        }
        switch (2) {
            case 1:
                echo "one\n";
                break;
            case 2:
                echo "two\n";
                break;
            default:
                echo "other\n";
        }
        string $grade = "B";
        switch ($grade) {
            case "A":
                echo "great\n";
                break;
            case "B":
                echo "good\n";
                break;
        }

        // 函数
        echo "== fn ==\n";
        echo fib(10), "\n";       // 55
        echo sumTo(100), "\n";    // 5050

        // 数组
        echo "== array ==\n";
        array<int> $arr = [10, 20, 30];
        $arr[] = 40;
        $arr[0] = 11;
        echo len($arr), "\n";     // 4
        foreach ($arr as $k => $v) {
            echo $k, ":", $v, " ";
        }
        echo "\n";
        array<string> $names = ["php", "c"];
        foreach ($names as $name) {
            echo "[", $name, "]";
        }
        echo "\n";
        echo $arr[2] + $arr[3], "\n";  // 70
        array<array<int>> $grid = [[1, 2], [3, 4]];
        echo $grid[1][0], "\n";        // 3

        // 字符串
        echo "== string ==\n";
        string $name = "world";
        echo "hello $name, 1+2={$x}!\n";
        echo len("hello"), "\n";       // 5
        echo "abc" == "abc", "\n";     // true
        echo "abc" < "abd", "\n";      // true
        echo "hello"[1], "\n";         // e
        string $big = "tiny" . "php";
        echo $big, "\n";

        // 类与继承
        echo "== class ==\n";
        Animal $a = new Animal("generic");
        Dog $d = new Dog("rex");
        $a->speak();
        $d->speak();
        Animal $up = $d;               // 向上转型
        $up->speak();                  // 动态分发 → woof!
        echo $d->fetch(), "\n";
        echo $d->describe(), "\n";     // 继承方法 + protected 属性
        Animal $nil = null;
        echo $nil == null, "\n";       // true

        // 静态成员
        echo "== static ==\n";
        Counter::$count = 0;
        Counter $c1 = new Counter();
        Counter $c2 = new Counter();
        echo Counter::$count, "\n";    // 2
        echo $c2->id, "\n";            // 2

        // 强转与内置函数
        echo "== cast ==\n";
        int $n = (int)"123";
        echo $n + 1, "\n";             // 124
        double $pi = (double)"3.14";
        echo $pi * 2, "\n";            // 6.28
        string $t = (string)42;
        echo $t . "!", "\n";           // 42!
        bool $b = (bool)"x";
        echo $b, "\n";                 // true
        var_dump($x);                      // int(17)
        var_dump("hi");                    // string(2) "hi"
        var_dump($names);                  // array(2) [...]
        echo 1 == 1 && 2 > 1, "\n";    // true
        echo !false, "\n";             // true
        int $cmp = 5 > 3 ? 1 : 0;
        echo $cmp, "\n";               // 1
    }
}
