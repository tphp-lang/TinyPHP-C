<?php

// expect-error: 项目内相对路径

#include "../outside/evil.h"

class Main
{
    public function main(): void
    {
    }
}
