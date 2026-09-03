<?php

// expect-error: 与既有 case 重复

enum Tag: int
{
    case A = 1;
    case B = 1;
}

class Main
{
    public function main(): void
    {
    }
}
