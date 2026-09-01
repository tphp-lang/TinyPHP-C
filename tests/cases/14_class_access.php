<?php

// expect:
// animal:mimi meow 1
// 5
// hidden/a 3
// object(Cat)
// only-inside hidden/public-seen

// 可见性（private/protected/public）与静态成员：self:: / parent:: / ClassName::、
// 静态方法继承、私有静态属性/方法。

class Animal
{
    protected string $name;
    private static int $born = 0;
    public function __construct(string $name)
    {
        $this->name = $name;
        self::$born++;
    }
    protected function describe(): string
    {
        return "animal:" . $this->name;
    }
    public static function born(): int
    {
        return self::$born;
    }
}

class Cat extends Animal
{
    private string $nick = "kitty";
    public function speak(): string
    {
        return $this->describe() . " meow"; // protected 方法/属性子类可用
    }
    public function nickLen(): int
    {
        return len($this->nick);
    }
    public static function catBorn(): int
    {
        return parent::born();
    }
}

class Vault
{
    private string $key;
    public function __construct(string $key)
    {
        $this->key = $key;
    }
    private function unlock(): string
    {
        return "hidden/" . $this->key;
    }
    public function tell(): string
    {
        return $this->unlock(); // private 方法仅类内可达
    }
    private static function stamp(): string
    {
        return "only-inside";
    }
    public function mark(): string
    {
        return self::stamp();
    }
}

class Main
{
    public function main(): void
    {
        $cat = new Cat("mimi");
        echo $cat->speak(), " ", Cat::catBorn(), "\n"; // parent:: 静态转发
        echo $cat->nickLen(), "\n";                    // private 属性类内读

        $a = new Cat("a");
        $b = new Cat("a");
        $v = new Vault("a");
        echo $v->tell(), " ", Animal::born(), "\n";    // private 方法 + 静态计数跨实例

        var_dump($cat); // object(Cat)

        $w = new Vault("public-seen");
        echo $w->mark(), " ", $w->tell(), "\n";        // 私有静态方法类内调用
    }
}
