<?php

declare(strict_types=1);

namespace Tphp\Ast\expr;

use Tphp\Ast\Expr;

final class BoolLit extends Expr
{
    public function __construct(public readonly bool $value)
    {
        parent::__construct();
    }
}
