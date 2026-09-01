<?php

// expect-error: 期望 c.f32，得到 float

class Main
{
    public function main(): void
    {
        float $d = 3.14;
        c.f32 $f = $d;
        echo $f, "\n";
    }
}
