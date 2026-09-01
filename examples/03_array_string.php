<?php

// 数组与字符串（类型自动推导风格）

function average(array<int> $values): double
{
    $sum = 0;
    foreach ($values as $v) {
        $sum += $v;
    }
    return (double)$sum / (double)len($values);
}

class Main
{
    public function main(): void
    {
        $scores = [92, 87, 95, 78, 88];
        echo "平均分: ", average($scores), "\n";

        // 追加与下标
        $scores[] = 100;
        $scores[0] = 93;
        var_dump($scores);

        // 嵌套数组
        $matrix = [[1, 0, 0], [0, 1, 0]];
        foreach ($matrix as $row) {
            foreach ($row as $cell) {
                echo $cell, " ";
            }
            echo "\n";
        }

        // 字符串插值与拼接
        $lang = "TinyPHP";
        $ver = "0.3";
        echo "$lang $ver — PHP 子集 → C\n";
        echo "长度: ", len($lang), "\n";
        echo "首字符: ", $lang[0], "\n";
    }
}
