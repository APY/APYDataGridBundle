<?php

namespace APY\DataGridBundle\DoctrineExtension;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * INSTR(exp1, exp2).
 *
 * More: http://dev.mysql.com/doc/refman/5.0/en/string-functions.html#function_instr
 */
class Instr extends FunctionNode
{
    public $str;
    public $substr;

    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->str = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->substr = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker)
    {
        return 'INSTR(' .
        $this->str->dispatch($sqlWalker) . ', ' .
        $this->substr->dispatch($sqlWalker) .
        ')';
    }
}
