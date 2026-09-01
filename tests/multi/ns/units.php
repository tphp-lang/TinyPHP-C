namespace Geom;

// 与 geometry.php 同命名空间（跨文件同一作用域）。
// 裸调用 globalHelper：本 ns 未定义 → 自动回退到全局命名空间（PHP 语义）。
class Units
{
    public static function label(): string
    {
        return globalHelper() . "/units";
    }
}
