<?php

namespace Primavera\Metadata;

trait ResolveTypeTrait
{
    public null | string | array $type = null;

    public $typeInfo;

    private $uses = [];

    private function resolveType()
    {
        $this->type = $type = $this->parseType();
        $this->typeInfo = $this->parseTypeDecoration($type);

        if (!$type) {
            $type = $this->getReflectionType();

            if ($type instanceof \ReflectionUnionType || $type instanceof \ReflectionIntersectionType) {
                $this->type = array_map(fn($t) => $t->getName(), $type->getTypes());

                return;
            }

            $this->type = $type?->getName();
        }
    }

    abstract private function getReflectionType();

    abstract private function getDocBlockTypePrefix();

    public function getDocComment()
    {
        return $this->getReflection()->getDocComment();
    }

    private function parseType()
    {
        $docComment = $this->getDocComment();

        preg_match(
            "/@{$this->getDocBlockTypePrefix()}\s+(?P<full>(?P<class>[^\[\s<]+)(?P<suffix>(\[\])|(<.+>))?)/",
            $docComment,
            $matches
        );

        $fullType = $matches['full'] ?? null;
        $type     = $matches['class'] ?? null;

        if (null === $type) {
            return;
        }

        if ($resolvedType = $this->resolveFullTypeName($type, $matches['suffix'] ?? null)) {
            return $resolvedType;
        }

        return $fullType;
    }

    private function resolveFullTypeName($type, $suffix = null) {
        $type = preg_replace('/^\?/', '', $type);

        if (preg_match('/^\\\/', $type)) {
            return preg_replace('/^\\\/', '', $type) . $suffix;
        }

        if (class_exists($type)) {
            return $type;
        }

        $uses = $this->getClassUses();
        $type = str_replace('\\', '\\\\', $type);

        foreach ($uses as $use) {
            if (preg_match("/{$type}$/", $use)) {
                return $use . $suffix;
            }

            if (class_exists("$use\\$type")) {
                return "$use\\$type" . $suffix;
            }
        }
    }

    private function getClassUses(): array
    {
        if ($this->uses) {
            return $this->uses;
        }

        $filename = $this->getReflection()->getDeclaringClass()->getFileName();
        
        if (is_file($filename)) {
            $contents = file_get_contents($filename);
            
            preg_match_all('/use\s+(.*);/', $contents, $matches);
            
            $uses = $matches[1] ?? [];
            
            $matches = [];
            
            preg_match('/namespace\s+(.*);/', $contents, $matches);
            
            if (!empty($matches[1])) {
                array_push($uses, $matches[1]);
            }
            
            return $this->uses = $uses;
        }
        
        return [];
    }

    public function getParsedType()
    {
        if (isset($this->type)) {
            return preg_replace('/(\[\]$)|(\<\>$)/', '', $this->type);
        }
    }
    
    public function isNativeType(): bool
    {
        return in_array($this->type, [
            'string',
            'array',
            'int',
            'integer',
            'float',
            'boolean',
            'bool',
            'DateTime',
            '\DateTime',
            '\DateTimeImmutable',
            'DateTimeImmutable',
        ]);
    }
    
    public function isDecoratedType(): bool
    {
        return (bool) preg_match('/(.*)((\<(.*)\>)|(\[\]))/', $this->type);
    }
    
    public function isDateType(): bool
    {
        $type = $this->isDecoratedType() ? $this->typeInfo['class'] ?? $this->type : $this->type;
        
        return in_array($type, ['DateTime', '\DateTime', 'DateTimeImmutable', '\DateTimeImmutable']);
    }
    
    private function parseTypeDecoration(string $type = null)
    {
        if (preg_match('/(?P<class>.*)((\<(?P<decoration>.*)\>)|(?P<brackets>\[\]))/', $type, $matches)) {
            $decoration = isset($matches['brackets']) ? $matches['class'] : $matches['decoration'];

            return [
                'class' => isset($matches['brackets'])
                    ? 'array'
                    : $this->resolveFullTypeName($matches['class']) ?? $matches['class'],
                'decoration' => $this->resolveFullTypeName($decoration) ?? $decoration
            ];
        }
    }

    public function getType(): string | array | null
    {
        return $this->type;
    }
}
