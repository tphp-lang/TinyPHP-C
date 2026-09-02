<?php

// expect-error: 不允许编译器选项

#flag -B/tmp/evil-gcc

class Main
{
    public function main(): void
    {
    }
}
