<?php

// expect:
// while 0
// while 1
// while 2
// do-once
// for 0
// for 2
// two
// good
// 55
// 5050
// big
// small-eq

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

function classify(int $n): string
{
    if ($n > 10) {
        return "big";
    } elseif ($n == 10) {
        return "small-eq";
    }
    return "small";
}

class Main
{
    public function main(): void
    {
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
        switch ("B") {
            case "A":
                echo "great\n";
                break;
            case "B":
                echo "good\n";
                break;
        }
        echo fib(10), "\n";
        echo sumTo(100), "\n";
        echo classify(99), "\n";
        echo classify(10), "\n";
    }
}
