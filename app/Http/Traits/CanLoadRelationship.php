<?php
namespace App\Http\Traits;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as QueryBuilder;


trait CanLoadRelationship
{
    /*
    Este es un trait usado en controladores
    Sirve para  elegir que clases devolver en una respuesta API
    */

    /**
     * Verifies that a given relation is included in query params
     * @param string $relation
     * @return bool
     */
    protected function shouldIncludeRelation(string $relation): bool
    {

        $include = request()->query('include');

        if (!$include) {
            return false;
        }
        $relations = array_map('trim', explode(',', $include));

        return in_array($relation, $relations);

    }

    /**
     *  Loads all indicated relationships of a given resource if indicated in query params
     * @param \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\HasMany $for
     * @param mixed $relations
     * @return EloquentBuilder|HasMany|Model|QueryBuilder
     */
    public function loadRelationship(
        Model|QueryBuilder|EloquentBuilder|HasMany $for,
        ?array $relations = null
    ): Model|QueryBuilder|EloquentBuilder|HasMany {

        $relations = $relations ?? $this->relations ?? [];
        foreach ($relations as $relation) {
            $for->when(
                $this->shouldIncludeRelation($relation),
                fn($q) => $for instanceof Model ? $for->load($relation) : $q->with($relation)
            );
        }
        return $for;
    }


}
