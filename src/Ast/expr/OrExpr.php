<?php

declare(strict_types=1);

namespace Tphp\Ast\expr;

use Tphp\Ast\Expr;
use Tphp\Ast\Stmt;

/**
 * 错误处理：f() or { ... }（仅函数调用可带）。
 *
 * 调用发生错误时执行块；块内 err 为只读错误消息（string）。
 * 值上下文取块内最后一条表达式语句的值；块内可用 return/break/continue。
 *
 * @param list<Stmt> $block
 */
final class OrExpr extends Expr
{
    public function __construct(
        public readonly Expr $call,
        public readonly array $block,
    ) {
        parent::__construct();
    }
}
