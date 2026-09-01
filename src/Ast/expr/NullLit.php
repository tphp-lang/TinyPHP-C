<?php

declare(strict_types=1);

namespace Tphp\Ast\expr;

use Tphp\Ast\Expr;

final class NullLit extends Expr
{
    public function __construct()
    {
        parent::__construct();
    }
}
