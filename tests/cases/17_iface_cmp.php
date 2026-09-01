<?php

// expect:
// true
// false
// true

interface Animal
{
    public function name(): string;
}

interface Dog extends Animal
{
    public function bark(): void;
}

class Husky implements Dog
{
    public function name(): string
    {
        return "husky";
    }

    public function bark(): void
    {
    }
}

class Main
{
    public function main(): void
    {
        Dog $d = new Husky();
        Animal $a = $d; // 子接口值可赋给父接口变量
        echo $a == $d, "\n";
        Dog $none = null;
        echo $a == $none, "\n";
        Animal $other = new Husky();
        echo $a != $other, "\n";
    }
}
