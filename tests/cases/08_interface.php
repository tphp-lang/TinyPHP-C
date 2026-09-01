<?php

// expect:
// circle=12
// rect=12
// rect area=10
// show: circle => 3
// rect/rect wxh
// true
// false

interface Shape
{
    public function area(): double;
    public function name(): string;
}

interface Describable extends Shape
{
    public function describe(): string;
}

class Circle implements Shape
{
    public double $r;
    public function __construct(double $r)
    {
        $this->r = $r;
    }
    public function area(): double
    {
        return 3.0 * $this->r * $this->r;
    }
    public function name(): string
    {
        return "circle";
    }
}

class Rect implements Describable
{
    public double $w;
    public double $h;
    public function __construct(double $w, double $h)
    {
        $this->w = $w;
        $this->h = $h;
    }
    public function area(): double
    {
        return $this->w * $this->h;
    }
    public function name(): string
    {
        return "rect";
    }
    public function describe(): string
    {
        return $this->name() . " wxh";
    }
}

function show(Shape $s): void
{
    echo "show: ", $s->name(), " => ", $s->area(), "\n";
}

class Main
{
    public function main(): void
    {
        // 多态数组：元素为接口胖指针
        array<Shape> $shapes = [new Circle(2.0), new Rect(3.0, 4.0)];
        foreach ($shapes as $s) {
            echo $s->name(), "=", $s->area(), "\n";
        }

        // 类 → 接口赋值
        Shape $s = new Rect(2.0, 5.0);
        echo "rect area=", $s->area(), "\n";

        // 接口传参
        show(new Circle(1.0));

        // 接口继承：Describable 变量可调用 Shape 的方法
        Describable $d = new Rect(2.0, 3.0);
        echo $d->name(), "/", $d->describe(), "\n";

        // null 比较
        Shape $nil = null;
        echo $nil == null, "\n";
        echo $s == null, "\n";
    }
}
