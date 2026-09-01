<?php

// expect:
// 7
// 9
// 3
// 1
// 1024
// -4
// 40 15
// 1 7 6 -6
// 17
// abc
// 3.75
// 3.1428571428571

class Main
{
    public function main(): void
    {
        echo 1 + 2 * 3, "\n";
        echo (1 + 2) * 3, "\n";
        echo 7 / 2, "\n";         // C 整除
        echo 7 % 3, "\n";
        echo 2 ** 10, "\n";
        echo -2 ** 2, "\n";       // ** 优先于一元减
        echo 10 << 2, " ", 255 >> 4, "\n";
        echo 5 & 3, " ", 5 | 3, " ", 5 ^ 3, " ", ~5, "\n";

        int $x = 5;
        $x++;
        ++$x;
        $x += 10;
        echo $x, "\n";
        string $s = "a";
        $s .= "bc";
        echo $s, "\n";

        double $f = 1.5 + 2.25;
        echo $f, "\n";
        double $pi = 22.0 / 7.0;
        echo $pi, "\n";
    }
}
