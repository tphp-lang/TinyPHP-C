namespace Geom;

const double PRECISION = 0.001;

interface Shape
{
    public function area(): double;
    public function name(): string;
}

class Rect implements Shape
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
}

function area(Shape $s): double
{
    return $s->area();
}

function dup(double $v): double
{
    return $v * 2.0;
}
