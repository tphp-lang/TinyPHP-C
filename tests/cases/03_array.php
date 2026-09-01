<?php

// expect:
// 4
// 0:11 1:20 2:30 3:40 
// [php][c]
// 70
// 3
// array(2) [array(2) [1, 2], array(2) [3, 4]]
// sum=101

class Main
{
    public function main(): void
    {
        array<int> $arr = [10, 20, 30];
        $arr[] = 40;
        $arr[0] = 11;
        echo len($arr), "\n";
        foreach ($arr as $k => $v) {
            echo $k, ":", $v, " ";
        }
        echo "\n";
        array<string> $names = ["php", "c"];
        foreach ($names as $name) {
            echo "[", $name, "]";
        }
        echo "\n";
        echo $arr[2] + $arr[3], "\n";
        array<array<int>> $grid = [[1, 2], [3, 4]];
        echo $grid[1][0], "\n";
        var_dump($grid);

        int $total = 0;
        foreach ($arr as $v) {
            $total += $v;
        }
        echo "sum=", $total, "\n";
    }
}
