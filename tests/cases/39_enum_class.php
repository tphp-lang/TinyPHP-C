<?php

// 枚举类：int/string backed + 纯枚举，case 单例恒等、->name/->value、
// 方法/$this、接口实现、from/tryFrom/cases
// expect:
// hearts=H
// red
// true
// false
// H D C S
// Draft
// 2
// Draft
// nil
// 2
// yes
// H 1
// identity-ok

interface HasColor
{
    public function color(): string;
}

enum Suit: string implements HasColor
{
    case Hearts = 'H';
    case Diamonds = 'D';
    case Clubs = 'C';
    case Spades = 'S';

    public function color(): string
    {
        if ($this == Suit::Hearts || $this == Suit::Diamonds) {
            return "red";
        }
        return "black";
    }

    public static function fallback(): Suit
    {
        return Suit::Hearts;
    }
}

enum Level: int
{
    case Low = 1;
    case Mid = 2;
    case High = 3;

    public function bumped(): Level
    {
        if ($this == Level::High) {
            return Level::High;
        }
        return Level::from($this->value + 1);
    }
}

enum State
{
    case Draft;
    case Published;
}

class Main
{
    public function main(): void
    {
        // case 引用 + ->value + 方法（$this）
        Suit $s = Suit::Hearts;
        echo "hearts=", $s->value, "\n";
        echo $s->color(), "\n";

        // 恒等比较：同 case 同实例
        echo $s == Suit::Hearts, "\n";
        echo $s == Suit::Spades, "\n";

        // cases() 按声明序
        foreach (Suit::cases() as $c) {
            echo $c->value, " ";
        }
        echo "\n";

        // 纯枚举：->name、恒等
        State $st = State::Draft;
        echo $st->name, "\n";
        echo len(State::cases()), "\n";
        echo $st == State::Published ? "Published" : $st->name, "\n";

        // tryFrom / from
        echo Suit::tryFrom("X") == null ? "nil" : "set", "\n";
        echo Level::from(2)->value, "\n";

        // 接口约束：枚举 case 赋给接口类型变量
        HasColor $h = Suit::Spades;
        echo $h->color() == "black" ? "yes" : "no", "\n";

        // 静态方法 + int backed ->value
        echo Suit::fallback()->value, " ", Level::Low->value, "\n";

        // 恒等经 from 往返
        echo Suit::from("H") == Suit::Hearts ? "identity-ok" : "broken", "\n";
    }
}
