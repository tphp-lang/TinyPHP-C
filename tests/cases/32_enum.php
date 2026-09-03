<?php

// #enum：C 枚举常量集（成员名 = 枚举名_成员名，c.i32 常量，可参与运算与比较）
// C 语义：首成员缺省 = 0，其后 = 前值 + 1
// expect:
// 1 2
// 4
// color=2
// 4

#enum Color {
    RED = 1,
    GREEN,
    BLUE = 4,
}

#enum State {
    IDLE,
    BUSY,
}

class Main
{
    public function main(): void
    {
        echo Color_RED, " ", Color_GREEN, "\n";
        echo Color_BLUE, "\n";
        c.i32 $c = Color_GREEN;
        echo "color=", $c, "\n";
        echo State_IDLE + State_BUSY + 3, "\n";
    }
}
