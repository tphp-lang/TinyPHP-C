<?php

// 控制流：if / while / for / foreach / switch

function fizzbuzz(int $n): string
{
    if ($n % 15 == 0) {
        return "FizzBuzz";
    } elseif ($n % 3 == 0) {
        return "Fizz";
    } elseif ($n % 5 == 0) {
        return "Buzz";
    }
    return (string)$n;
}

class Main
{
    public function main(): void
    {
        for (int $i = 1; $i <= 15; $i++) {
            echo fizzbuzz($i), " ";
        }
        echo "\n";

        // 阶乘：while
        int $n = 10;
        int $acc = 1;
        while ($n > 1) {
            $acc *= $n;
            $n--;
        }
        echo "10! = ", $acc, "\n";

        // switch：PHP 语义，不隐式穿透
        switch (2026) {
            case 2000:
                echo "millennium\n";
                break;
            case 2026:
                echo "this year\n";
                break;
            default:
                echo "?\n";
        }
    }
}
