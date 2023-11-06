<?php

namespace Primavera\Persistence\Parser;

use Doctrine\Common\Lexer\AbstractLexer;
use Doctrine\Common\Lexer\Token;

class TokenWalker implements WalkerInterface
{
    private AbstractLexer $lexer;

    private array $handlers = [];

    private array $syntax;

    private array $syntaxRootTypes;

    private ?Token $lastToken = null;

    private Token $token;

    public function __construct(AbstractLexer $lexer, array $syntax, array $syntaxRootTypes)
    {
        $this->lexer = $lexer;
        $this->syntax = $syntax;
        $this->syntaxRootTypes = $syntaxRootTypes;
    }

    public static function create(AbstractLexer $lexer, array $syntax, array $syntaxRootTypes): self
    {
        return new self($lexer, $syntax, $syntaxRootTypes);
    }

    protected function validateSyntax(): void
    {
        if (!$this->lastToken && !in_array($this->token->type, $this->syntaxRootTypes)) {
            throw new \CompileError("invalid start operator {$this->token->value}");
        }

        if ($this->lastToken && !in_array($this->token->type, $this->syntax[$this->lastToken->type])) {
            throw new \CompileError("{$this->token->value} cannot be used after {$this->lastToken->value}");
        }
    }

    public function on($type, callable $handler): self
    {
        $this->handlers[$type] = $handler;

        return $this;
    }

    public function checkNextType($type, $throwError = true): bool
    {
        if (!$this->lexer->lookahead) {
            if ($throwError)
                throw new \CompileError("Missing type {$type}, input is incomplete");

            return false;
        }

        if (is_scalar($type) && !$this->lexer->lookahead->isA($type)) {
            if ($throwError)
                throw new \CompileError("expected type {$type}, but found {$this->lexer->lookahead->type}");

            return false;
        } elseif (is_array($type) && !in_array($this->lexer->lookahead->type, $type)) {
            $expected = implode(' or ', $type);

            if ($throwError)
                throw new \CompileError("expected type {$expected}, but found {$this->lexer->lookahead->type}");

            return false;
        }

        return true;
    }

    /**
     * @param scalar ...$types
     * @return array<scalar>
     */
    public function getSequence(...$types)
    {
        $sequence = [];

        foreach ($types as $type) {
            $this->checkNextType($type);

            $this->lexer->moveNext();
            $sequence[] = $this->lexer->token;
        }

        $this->lastToken = $this->lexer->token;

        return $sequence;
    }

    /**
     * @param scalar ...$types
     * @return iterable<array<scalar>>
     */
    public function getWhileSequence(...$types): iterable
    {
        while (true) {
            try {
                yield $this->getSequence(...$types);
            } catch (\CompileError $e) {
                break;
            }
        }
    }

    /**
     * @param callable $checker
     * @param int|null $atLeast
     * @param string|null $errorMessage
     * @return iterable<Token>
     */
    public function getWhile(callable $checker, int $atLeast = null, string $errorMessage = null): iterable
    {
        $count = 0;
        $expected = null;

        while($checker($this->lexer)) {
            $count++;
            $this->lexer->moveNext();

            yield $this->lexer->token;

            $expected = $this->lexer->token->type;
        }

        if ($atLeast && $atLeast < $count) {
            throw new \CompileError($errorMessage ?? "expected at least a sequence of {$atLeast} of type {$expected}");
        }

        $this->lastToken = $this->lexer->token;
    }

    /**
     * @param scalar $type
     * @return iterable<Token>
     */
    public function getWhileIsType($type): iterable
    {
        yield from $this->getWhile(fn(AbstractLexer $lexer) => $lexer->lookahead && $this->checkNextType($type, false));
    }

    public function getNextIfType($type): Token
    {
        $this->checkNextType($type);

        return $this->getNext();
    }

    public function getNext(): Token
    {
        $this->lastToken = $this->lexer->token;
        $this->lexer->moveNext();
        $this->token = $this->lexer->token;

        return $this->lexer->token;
    }

    /**
     * @param iterable<Token> $tokens
     * @return scalar[]
     */
    public function getValues(iterable $tokens)
    {
        return \iterator_map($tokens, fn($t) => $t->value);
    }

    public function getLexer(): AbstractLexer
    {
        return $this->lexer;
    }

    public function walk()
    {
        $this->lexer->reset();
        $this->lexer->moveNext();

        while ($this->lexer->lookahead) {
            $this->lexer->moveNext();
            $this->token = $this->lexer->token;
            $this->validateSyntax();

            if (isset($this->handlers[$this->token->type])) {
                $this->handlers[$this->token->type]($this->token, $this->lastToken, $this);
            }

            $this->lastToken = $this->lexer->token;
        }
    }
}