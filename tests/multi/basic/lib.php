// 这个文件故意不写 <?php —— 开标签是可选的（与旧版一致）

const int MAX_LIMIT = 100;
const GREETING = "hello";

function add(int $a, int $b): int
{
    return $a + $b;
}

class Calculator
{
    public const double PI = 3.14;
    public function area(double $r): double
    {
        return self::PI * $r * $r;
    }
}
