<?php

// 类：属性 / 方法 / 构造器 / 继承 / 多态 / 静态成员

class Shape
{
    public string $name;
    public function __construct(string $name)
    {
        $this->name = $name;
    }
    public function area(): double
    {
        return 0.0;
    }
    public function describe(): string
    {
        return $this->name . " 的面积是 " . (string)$this->area();
    }
}

class Circle extends Shape
{
    public double $radius;
    public function __construct(double $radius)
    {
        parent::__construct("circle");
        $this->radius = $radius;
    }
    public function area(): double
    {
        return 3.14159265 * $this->radius * $this->radius;
    }
}

class Rect extends Shape
{
    public double $w;
    public double $h;
    public function __construct(double $w, double $h)
    {
        parent::__construct("rect");
        $this->w = $w;
        $this->h = $h;
    }
    public function area(): double
    {
        return $this->w * $this->h;
    }
}

class Counter
{
    public static int $instances = 0;
    public function __construct()
    {
        Counter::$instances += 1;
    }
}

class Main
{
    public function main(): void
    {
        // 多态数组：经 vtable 动态分发
        array<Shape> $shapes = [new Circle(2.0), new Rect(3.0, 4.0)];
        foreach ($shapes as $shape) {
            echo $shape->describe(), "\n";
        }

        Counter $a = new Counter();
        Counter $b = new Counter();
        echo "实例数: ", Counter::$instances, "\n";
    }
}
