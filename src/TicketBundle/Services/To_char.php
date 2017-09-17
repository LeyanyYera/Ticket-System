<?php

namespace TicketBundle\Services;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;

class To_char extends FunctionNode {

    public $date = null;
    public $format = null;

    // parsear la funcion y establecer las partes
    public function parse(\Doctrine\ORM\Query\Parser $parser) {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->date = $parser->ArithmeticExpression();
        $parser->match(Lexer::T_COMMA);
        $this->format = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    // devuelve el sql nativo
    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker) {
        return "to_char(".$this->date->dispatch($sqlWalker).",".
        $this->format->dispatch($sqlWalker).")";
    }

}
