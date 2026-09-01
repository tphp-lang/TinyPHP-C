<?php

declare(strict_types=1);

namespace Tphp\Ast\expr;

use Tphp\Ast\Expr;

/** 属性读取：$obj->field。 */
final class PropFetch extends Expr
{
    public function __construct(
        public readonly Expr $obj,
        public readonly string $name,
    ) {
        parent::__construct();
    }
}
