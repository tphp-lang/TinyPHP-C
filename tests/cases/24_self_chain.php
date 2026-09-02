<?php

// expect:
// 1+2i
// 4+6i
// 8+12i

class Complex
{
    public c.i32 $re;
    public c.i32 $im;

    public function __construct(c.i32 $re, c.i32 $im)
    {
        $this->re = $re;
        $this->im = $im;
    }

    // : self 链式：返回 $this 继续操作
    public function add(c.i32 $re, c.i32 $im): self
    {
        $this->re = $this->re + $re;
        $this->im = $this->im + $im;
        return $this;
    }

    public function show(): void
    {
        echo $this->re, "+", $this->im, "i", "\n";
    }
}

class Main
{
    public function main(): void
    {
        Complex $c = new Complex(1, 2);
        $c->show();
        // 链式：add 返回 $this，继续 add
        $c->add(2, 2)->add(1, 2)->show();
        $c->add(4, 6)->show();
    }
}
