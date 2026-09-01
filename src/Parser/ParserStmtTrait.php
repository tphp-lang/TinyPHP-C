<?php

declare(strict_types=1);

namespace Tphp\Parser;

use Tphp\Ast\Stmt;
use Tphp\Ast\expr\VarExpr;
use Tphp\Ast\stmt\BlockStmt;
use Tphp\Ast\stmt\BreakStmt;
use Tphp\Ast\stmt\CaseClause;
use Tphp\Ast\stmt\ContinueStmt;
use Tphp\Ast\stmt\DoWhileStmt;
use Tphp\Ast\stmt\EchoStmt;
use Tphp\Ast\stmt\ExprStmt;
use Tphp\Ast\stmt\ForeachStmt;
use Tphp\Ast\stmt\ForStmt;
use Tphp\Ast\stmt\IfStmt;
use Tphp\Ast\stmt\LocalConstStmt;
use Tphp\Ast\stmt\LocalDecl;
use Tphp\Ast\stmt\ReturnStmt;
use Tphp\Ast\stmt\SwitchStmt;
use Tphp\Ast\stmt\ThrowStmt;
use Tphp\Ast\stmt\WhileStmt;
use Tphp\Token\TokenKind;

/** 语句解析：控制流 / echo / 局部声明 / 表达式语句。 */
trait ParserStmtTrait
{
    private function parseStmt(): Stmt
    {
        $t = $this->peek();
        $kind = $t->kind;

        $stmt = match (true) {
            $kind === TokenKind::KwIf => $this->parseIf(),
            $kind === TokenKind::KwWhile => $this->parseWhile(),
            $kind === TokenKind::KwDo => $this->parseDoWhile(),
            $kind === TokenKind::KwFor => $this->parseFor(),
            $kind === TokenKind::KwForeach => $this->parseForeach(),
            $kind === TokenKind::KwSwitch => $this->parseSwitch(),
            $kind === TokenKind::KwBreak => $this->parseBreakOrContinue(true),
            $kind === TokenKind::KwContinue => $this->parseBreakOrContinue(false),
            $kind === TokenKind::KwReturn => $this->parseReturn(),
            $kind === TokenKind::KwThrow => $this->parseThrow(),
            $kind === TokenKind::KwEcho => $this->parseEcho(),
            $kind === TokenKind::KwConst => $this->parseLocalConst(),
            $kind === TokenKind::Lbrace => $this->parseBlockStmt(),
            $this->stmtStartsDecl() => $this->parseLocalDecl(),
            default => null,
        };
        if ($stmt === null) {
            // 表达式语句：expr ';'
            $expr = $this->parseExpr();
            $this->expect(TokenKind::Semicolon, "';'");
            $stmt = new ExprStmt($expr);
        }
        $stmt->pos ??= $t->pos;
        return $stmt;
    }

    private function parseIf(bool $elseif = false): Stmt
    {
        $this->expect($elseif ? TokenKind::KwElseIf : TokenKind::KwIf, $elseif ? "'elseif'" : "'if'");
        $this->expect(TokenKind::Lparen, "'('");
        $cond = $this->parseExpr();
        $this->expect(TokenKind::Rparen, "')'");
        $then = $this->parseBracedBlock();

        $else = null;
        if ($this->is(TokenKind::KwElseIf)) {
            $else = [$this->parseIf(true)]; // elseif 脱糖为 else 中的嵌套 if
        } elseif ($this->match(TokenKind::KwElse)) {
            $else = $this->parseBracedBlock();
        }
        return new IfStmt($cond, $then, $else);
    }

    private function parseWhile(): Stmt
    {
        $this->expect(TokenKind::KwWhile, "'while'");
        $this->expect(TokenKind::Lparen, "'('");
        $cond = $this->parseExpr();
        $this->expect(TokenKind::Rparen, "')'");
        return new WhileStmt($cond, $this->parseBracedBlock());
    }

    private function parseDoWhile(): Stmt
    {
        $this->expect(TokenKind::KwDo, "'do'");
        $body = $this->parseBracedBlock();
        $this->expect(TokenKind::KwWhile, "'while'");
        $this->expect(TokenKind::Lparen, "'('");
        $cond = $this->parseExpr();
        $this->expect(TokenKind::Rparen, "')'");
        $this->expect(TokenKind::Semicolon, "';'");
        return new DoWhileStmt($body, $cond);
    }

    private function parseFor(): Stmt
    {
        $this->expect(TokenKind::KwFor, "'for'");
        $this->expect(TokenKind::Lparen, "'('");

        $init = null;
        if (!$this->is(TokenKind::Semicolon)) {
            if ($this->peekKind()->isTypeStart()) {
                $init = $this->parseLocalDecl(false);
            } else {
                $init = new ExprStmt($this->parseExpr());
            }
        }
        $this->expect(TokenKind::Semicolon, "';'");

        $cond = null;
        if (!$this->is(TokenKind::Semicolon)) {
            $cond = $this->parseExpr();
        }
        $this->expect(TokenKind::Semicolon, "';'");

        $post = null;
        if (!$this->is(TokenKind::Rparen)) {
            $post = $this->parseExpr();
        }
        $this->expect(TokenKind::Rparen, "')'");

        return new ForStmt($init, $cond, $post, $this->parseBracedBlock());
    }

    private function parseForeach(): Stmt
    {
        $this->expect(TokenKind::KwForeach, "'foreach'");
        $this->expect(TokenKind::Lparen, "'('");
        $arr = $this->parseExpr();
        $this->expect(TokenKind::KwAs, "'as'");

        $first = substr($this->expect(TokenKind::Var, '循环变量')->lit, 1);
        $keyVar = '';
        $valVar = $first;
        if ($this->match(TokenKind::FatArrow)) {
            $keyVar = $first;
            $valVar = substr($this->expect(TokenKind::Var, '值变量')->lit, 1);
        }
        $this->expect(TokenKind::Rparen, "')'");
        return new ForeachStmt($arr, $keyVar, $valVar, $this->parseBracedBlock());
    }

    private function parseSwitch(): Stmt
    {
        $this->expect(TokenKind::KwSwitch, "'switch'");
        $this->expect(TokenKind::Lparen, "'('");
        $cond = $this->parseExpr();
        $this->expect(TokenKind::Rparen, "')'");
        $this->expect(TokenKind::Lbrace, "'{'");

        $cases = [];
        while (!$this->is(TokenKind::Rbrace) && !$this->is(TokenKind::Eof)) {
            if ($this->match(TokenKind::KwCase)) {
                $cond2 = $this->parseExpr();
                $this->expect(TokenKind::Colon, "':'");
                $cases[] = new CaseClause($cond2, $this->parseCaseBody());
                continue;
            }
            if ($this->match(TokenKind::KwDefault)) {
                $this->expect(TokenKind::Colon, "':'");
                $cases[] = new CaseClause(null, $this->parseCaseBody());
                continue;
            }
            $this->errHere("switch 中期望 case 或 default");
            $this->next();
        }
        $this->expect(TokenKind::Rbrace, "'}'");
        return new SwitchStmt($cond, $cases);
    }

    /** case 体内的语句，直到下一个 case / default / 右花括号。 */
    private function parseCaseBody(): array
    {
        $stmts = [];
        while (!$this->is(TokenKind::KwCase) && !$this->is(TokenKind::KwDefault)
            && !$this->is(TokenKind::Rbrace) && !$this->is(TokenKind::Eof)) {
            $stmts[] = $this->parseStmt();
        }
        return $stmts;
    }

    private function parseBreakOrContinue(bool $isBreak): Stmt
    {
        $this->next();
        $this->expect(TokenKind::Semicolon, "';'");
        return $isBreak ? new BreakStmt() : new ContinueStmt();
    }

    private function parseReturn(): Stmt
    {
        $this->expect(TokenKind::KwReturn, "'return'");
        $expr = null;
        if (!$this->is(TokenKind::Semicolon)) {
            $expr = $this->parseExpr();
        }
        $this->expect(TokenKind::Semicolon, "';'");
        return new ReturnStmt($expr);
    }

    private function parseEcho(): Stmt
    {
        $this->expect(TokenKind::KwEcho, "'echo'");
        $parts = [$this->parseExpr()];
        while ($this->match(TokenKind::Comma)) {
            $parts[] = $this->parseExpr();
        }
        $this->expect(TokenKind::Semicolon, "';'");
        return new EchoStmt($parts);
    }

    private function parseBlockStmt(): Stmt
    {
        $this->expect(TokenKind::Lbrace, "'{'");
        $stmts = [];
        while (!$this->is(TokenKind::Rbrace) && !$this->is(TokenKind::Eof)) {
            $stmts[] = $this->parseStmt();
        }
        $this->expect(TokenKind::Rbrace, "'}'");
        return new BlockStmt($stmts);
    }

    /**
     * 局部变量声明：Type $name (= expr)? ';'。
     * for 循环初始化段用 $consumeSemi=false。
     */
    private function parseLocalDecl(bool $consumeSemi = true): Stmt
    {
        $typeRef = $this->parseTypeRef();
        $name = substr($this->expect(TokenKind::Var, '变量名')->lit, 1);
        $init = null;
        if ($this->match(TokenKind::Eq)) {
            $init = $this->parseExpr();
        }
        if ($consumeSemi) {
            $this->expect(TokenKind::Semicolon, "';'");
        }
        return new LocalDecl($typeRef, $name, $init);
    }

    /**
     * 语句是否为局部变量声明。
     * Ident 开头时需要前瞻区分：`Dog $d = ...` 是声明，
     * `Counter::$x = ...` / `foo()` 是表达式语句。
     */
    private function stmtStartsDecl(): bool
    {
        $kind = $this->peekKind();
        if ($kind === TokenKind::Ident) {
            $i = 1;
            // c.i8 $x = ...（c.* 别名）
            if ($this->peekKindAt($i) === TokenKind::Dot && $this->peekKindAt($i + 1) === TokenKind::Ident) {
                $i += 2;
            }
            // 指针后缀：Dog* / c.char*（可多个星）
            while ($this->peekKindAt($i) === TokenKind::Star) {
                $i++;
            }
            return $this->peekKindAt($i) === TokenKind::Var;
        }
        return $kind->isTypeStart();
    }

    private function parseThrow(): Stmt
    {
        $this->expect(TokenKind::KwThrow, "'throw'");
        $expr = $this->parseExpr();
        $this->expect(TokenKind::Semicolon, "';'");
        return new ThrowStmt($expr);
    }

    /** 函数内常量：const [TYPE] NAME = 字面量; */
    private function parseLocalConst(): Stmt
    {
        $this->expect(TokenKind::KwConst, "'const'");
        $typeRef = $this->peekIsConstTypeStart() ? $this->parseTypeRef() : null;
        $name = $this->expect(TokenKind::Ident, '常量名')->lit;
        $this->expect(TokenKind::Eq, "'='");
        $value = $this->parseUnary(); // 字面量 + 一元（Checker 校验）
        $this->expect(TokenKind::Semicolon, "';'");
        return new LocalConstStmt($name, $typeRef, $value);
    }

    /** @return list<object> */
    private function parseBracedBlock(): array
    {
        $this->expect(TokenKind::Lbrace, "'{'");
        $stmts = [];
        while (!$this->is(TokenKind::Rbrace) && !$this->is(TokenKind::Eof)) {
            $stmts[] = $this->parseStmt();
        }
        $this->expect(TokenKind::Rbrace, "'}'");
        return $stmts;
    }
}
