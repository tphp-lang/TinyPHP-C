<?php

// expect:
// generic makes a sound
// rex: woof!
// rex: woof!
// rex fetches the ball
// rex has 4 legs
// true
// 2
// 2
// kit: meow

class Animal
{
    public string $name;
    protected int $legs = 4;
    public function __construct(string $name)
    {
        $this->name = $name;
    }
    public function speak(): void
    {
        echo $this->name, " makes a sound\n";
    }
    public function describe(): string
    {
        return $this->name . " has " . (string)$this->legs . " legs";
    }
}

class Dog extends Animal
{
    public function speak(): void
    {
        echo $this->name, ": woof!\n";
    }
    public function fetch(): string
    {
        return $this->name . " fetches the ball";
    }
}

class Cat extends Animal
{
    public function speak(): void
    {
        echo $this->name, ": meow\n";
    }
}

class Counter
{
    public static int $count = 0;
    public int $id;
    public function __construct()
    {
        Counter::$count += 1;
        $this->id = Counter::$count;
    }
}

class Main
{
    public function main(): void
    {
        Animal $a = new Animal("generic");
        Dog $d = new Dog("rex");
        $a->speak();
        $d->speak();
        Animal $up = $d; // 向上转型，动态分发
        $up->speak();
        echo $d->fetch(), "\n";
        echo $d->describe(), "\n";
        Animal $nil = null;
        echo $nil == null, "\n";

        Counter::$count = 0;
        Counter $c1 = new Counter();
        Counter $c2 = new Counter();
        echo Counter::$count, "\n";
        echo $c2->id, "\n";

        // 多态数组
        array<Animal> $zoo = [new Cat("kit")];
        foreach ($zoo as $animal) {
            $animal->speak();
        }
    }
}
