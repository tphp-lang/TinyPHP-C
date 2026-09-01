<?php

// expect:
// 100 hello -42 3.14
// 0.5 7 1.0
// 1.0
// 200

const int MAX = 100;
const GREETING = "hello";
const NEG = -42;
const PI_APPROX = 3.14;

class Base
{
    public const string VERSION = "1.0";
}

class App extends Base
{
    public const double RATE = 0.5;
    private const int SECRET = 7;
    public function secret(): int
    {
        return self::SECRET;
    }
    public function inherited(): string
    {
        return parent::VERSION;
    }
}

class Main
{
    public function main(): void
    {
        echo MAX, " ", GREETING, " ", NEG, " ", PI_APPROX, "\n";
        App $app = new App();
        echo App::RATE, " ", $app->secret(), " ", $app->inherited(), "\n";
        echo Base::VERSION, "\n";
        echo $this->twice(MAX), "\n";
    }

    private function twice(int $n): int
    {
        const FACTOR = 2;
        return $n * FACTOR;
    }
}
