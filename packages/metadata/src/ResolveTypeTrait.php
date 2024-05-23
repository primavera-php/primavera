<?php

namespace Primavera\Metadata;

trait ResolveTypeTrait
{
    public null | string | array $type = null;

    public $typeInfo;

    public $realType;

    public $isParsedType = false;

    private $uses = [];

    private function resolveType()
    {
        $this->type = $type = $this->parseType();
        $this->realType = $realType = $this->getRealType();

        if ($type) {
            $this->isParsedType = true;
            $this->typeInfo = $this->parseTypeDecoration($type);
        } else {
            $this->type = $realType;
        }
    }

    public function isParsedType(): bool
    {
        return $this->isParsedType;
    }

    public function hasRealType(): bool
    {
        return $this->getReflectionType() != null;
    }

    public function getRealType(): null | string | array
    {
        if ($this->realType != null) {
            return $this->realType;
        }

        $type = $this->getReflectionType();

        if ($type instanceof \ReflectionUnionType || $type instanceof \ReflectionIntersectionType) {
            $type = array_map(fn($t) => $t->getName(), $type->getTypes());

            return null;
        }

        return $type?->getName();
    }

    public function getTypeInfo(): ?array
    {
        return $this->typeInfo;
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
            "/@{$this->getDocBlockTypePrefix()}\s+(?P<full>(?P<class>[^\[\s<]+)(?P<suffix>(\[\])|(<(?P<decoration>.+)>))?)/",
            $docComment,
            $matches
        );

        $fullType = $matches['full'] ?? null;
        $type     = $matches['class'] ?? null;

        if (null === $type) {
            return;
        }

        $decoration = $matches['decoration'] ?? null;
        $suffix = $matches['suffix'] ?? null;

        if ($this->checkIsNativeType($type)) {
            if (!$suffix) {
                return $type;
            }

            if ($suffix == '[]' || ($decoration && $this->checkIsNativeType($decoration))) {
                return $type . $suffix;
            }

            return $type . "<{$this->resolveFullTypeName($decoration)}>";
        }

        if ($resolvedType = $this->resolveFullTypeName($type)) {
            if (!$suffix) {
                return $resolvedType;
            }

            if ($suffix == '[]' || ($decoration && $this->checkIsNativeType($decoration))) {
                return $resolvedType . $suffix;
            }

            return $resolvedType . "<{$this->resolveFullTypeName($decoration)}>";
        }

        return $fullType;
    }

    private function resolveFullTypeName($type) 
    {
        $type = preg_replace('/^\?/', '', $type);

        if (preg_match('/^\\\/', $type)) {
            return preg_replace('/^\\\/', '', $type);
        }

        if (class_exists($type) || interface_exists($type)) {
            return $type;
        }

        $uses = $this->getClassUses();
        $type = str_replace('\\', '\\\\', $type);

        foreach ($uses as $use) {
            if (preg_match("/(^{$type}$)|(\\\\{$type}$)/", $use)) {
                return $use;
            }

            $useType = "$use\\$type";

            if (class_exists($useType) || interface_exists($useType)) {
                return $useType;
            }
        }

        return $type;
    }

    private function getClassUses(): array
    {
        if ($this->uses) {
            return $this->uses;
        }

        $reflection = $this->getReflection();

        $filename = $reflection instanceof \ReflectionClass 
            ? $reflection->getFileName()
            : $reflection->getDeclaringClass()->getFileName();
        
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

    private function checkIsNativeType(string $type): bool
    {
        return in_array($type, [
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
    
    public function isNativeType(): bool
    {
        return $this->checkIsNativeType($this->type);
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
