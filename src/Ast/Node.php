<?php

declare(strict_types=1);

namespace Tphp\Ast;

use Tphp\Token\Pos;

/** 所有 AST 节点的基类：仅携带源码位置。 */
abstract class Node
{
    public ?Pos $pos = null;

    /** 供子类 parent::__construct() 调用。 */
    public function __construct() {}
}
