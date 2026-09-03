<?php

// expect-error: 不能实例化

enum State
{
    case Draft;
}

class Main
{
    public function main(): void
    {
        State $s = new State();
    }
}
