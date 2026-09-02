<?php

// expect-error: 项目内相对路径

#flag tests/../../windows/system32/evil.c

class Main
{
    public function main(): void
    {
    }
}
