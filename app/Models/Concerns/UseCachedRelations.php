<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LogicException;

trait UseCachedRelations
{
    public function loadCachedRelationships(array $relationships): self
    {
        foreach ($relationships as $relationship) {
            $this->loadCachedRelationship($relationship);
        }

        return $this;
    }

    public function loadCachedBelongsToMany(array $relationships): self
    {
        $this->assertCachedBelongsToManyDefined($relationships);

        foreach ($relationships as $relationName) {
            $config = $this->cachedBelongsToMany[$relationName];
            $targetClass = $this->cachedRelationClass($config, $relationName, '$cachedBelongsToMany');
            $relatedKeyName = $this->cachedBelongsToManyLookupKey($targetClass, $config);
            $ids = $this->cachedBelongsToManyIds($relationName, $config);

            $all = $this->cachedAllForClass($targetClass);
            $related = $ids->isEmpty()
                ? $all->take(0)
                : $all->whereIn($relatedKeyName, $ids->values()->all())->values();

            $this->setRelation($relationName, $related);
        }

        return $this;
    }

    public function loadCachedRelationship(string $relationship): self
    {
        $this->assertCachedRelationshipsDefined([$relationship]);

        $config = $this->cachedRelationshipConfig($relationship);
        $foreignKeyValue = $this->getAttribute($config['foreign_key']);

        Log::info("Foreign Key:" . $foreignKeyValue);
        if (!$foreignKeyValue) {
            $this->setRelation($config['relation'], null);

            return $this;
        }

        $related = $this->cachedFirstWhereForClass(
            $config['class'],
            $config['owner_key'],
            $foreignKeyValue
        );
        $this->setRelation($config['relation'], $related);

        return $this;
    }

    protected function cachedRelationshipConfig(string $relationship): array
    {
        $config = $this->cachedRelationships[$relationship];
        $class = $this->cachedRelationClass($config, $relationship, '$cachedRelationships');
        $foreignKey = null;
        $ownerKey = null;

        if (is_array($config)) {
            $foreignKey = $config['foreign_key'] ?? $config['foreignKey'] ?? null;
            $ownerKey = $config['owner_key'] ?? $config['ownerKey'] ?? null;
        }

        $foreignKey ??= $this->cachedRelationshipForeignKey($relationship);
        $ownerKey ??= $this->cachedRelationshipOwnerKey($relationship, $class);

        return [
            'relation' => $relationship,
            'class' => $class,
            'foreign_key' => $foreignKey,
            'owner_key' => $ownerKey,
        ];
    }

    protected function cachedRelationClass(mixed $config, string $relationship, string $propertyName): string
    {
        if (is_string($config)) {
            return $config;
        }

        if (is_array($config) && isset($config['class']) && is_string($config['class'])) {
            return $config['class'];
        }

        throw new LogicException(sprintf(
            '%s must define %s[%s][class] with a model class string.',
            static::class,
            $propertyName,
            $relationship
        ));
    }

    protected function cachedRelationshipForeignKey(string $relationName): string
    {
        if (method_exists($this, $relationName)) {
            $relation = $this->{$relationName}();

            if (method_exists($relation, 'getForeignKeyName')) {
                return $relation->getForeignKeyName();
            }
        }

        return Str::snake($relationName) . '_id';
    }

    protected function cachedRelationshipOwnerKey(string $relationName, string $class): string
    {
        if (method_exists($this, $relationName)) {
            $relation = $this->{$relationName}();

            if (method_exists($relation, 'getOwnerKeyName')) {
                return $relation->getOwnerKeyName();
            }
        }

        return $this->cachedClassPrimaryKey($class);
    }

    protected function cachedClassPrimaryKey(string $class): string
    {
        if (method_exists($class, 'primaryKey')) {
            return $class::primaryKey();
        }

        return 'id';
    }

    protected function cachedAllForClass(string $class): Collection
    {
        if (method_exists($class, 'collection')) {
            return $class::collection();
        }

        if (method_exists($class, 'all')) {
            return $class::all();
        }

        return collect();
    }

    protected function cachedFirstWhereForClass(string $class, string $key, $value): mixed
    {
        if (method_exists($class, 'collection')) {
            return $class::collection()->firstWhere($key, $value);
        }

        if (method_exists($class, 'firstWhere')) {
            return $class::firstWhere($key, $value);
        }

        if (method_exists($class, 'all')) {
            return $class::all()->firstWhere($key, $value);
        }

        return null;
    }

    protected function cachedBelongsToManyIds(string $relationName, mixed $config): Collection
    {
        if (is_array($config) && array_key_exists('attribute', $config)) {
            return $this->normalizeIdList($this->getAttribute($config['attribute']));
        }

        if (method_exists($this, $relationName)) {
            $relation = $this->{$relationName}();

            if ($relation instanceof BelongsToMany) {
                $pivotRelatedKey = $relation->getRelatedPivotKeyName();

                return $relation->pluck($pivotRelatedKey);
            }
        }

        if (is_string($config)) {
            return $this->normalizeIdList($this->getAttribute($config));
        }

        return collect();
    }

    protected function normalizeIdList(mixed $value): Collection
    {
        if ($value instanceof Collection) {
            return $value->values();
        }

        if (is_array($value)) {
            return collect($value)->values();
        }

        if ($value === null) {
            return collect();
        }

        return collect([$value]);
    }

    protected function cachedBelongsToManyLookupKey(string $class, mixed $config): string
    {
        if (is_array($config) && isset($config['key'])) {
            return $config['key'];
        }

        return $this->cachedClassPrimaryKey($class);
    }

    protected function assertCachedBelongsToManyDefined(array $relationships): void
    {
        if (!property_exists($this, 'cachedBelongsToMany') || !is_array($this->cachedBelongsToMany)) {
            throw new LogicException(sprintf(
                '%s must define protected array $cachedBelongsToMany = [\'relation\' => [\'class\' => Model::class, ...]];',
                static::class
            ));
        }

        foreach ($relationships as $relationship) {
            if (!array_key_exists($relationship, $this->cachedBelongsToMany)) {
                throw new LogicException(sprintf(
                    '%s must include %s in $cachedBelongsToMany.',
                    static::class,
                    $relationship
                ));
            }

            $this->cachedRelationClass($this->cachedBelongsToMany[$relationship], $relationship, '$cachedBelongsToMany');
        }
    }

    protected function assertCachedRelationshipsDefined(array $relationships): void
    {
        if (!property_exists($this, 'cachedRelationships') || !is_array($this->cachedRelationships)) {
            throw new LogicException(sprintf(
                '%s must define protected array $cachedRelationships with relation names.',
                static::class
            ));
        }

        foreach ($relationships as $relationship) {
            if (!array_key_exists($relationship, $this->cachedRelationships)) {
                throw new LogicException(sprintf(
                    '%s must include %s in $cachedRelationships.',
                    static::class,
                    $relationship
                ));
            }

            $this->cachedRelationClass($this->cachedRelationships[$relationship], $relationship, '$cachedRelationships');
        }
    }
}
