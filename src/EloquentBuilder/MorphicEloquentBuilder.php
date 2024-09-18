<?php


namespace MorphicAbstractEloquent\EloquentBuilder;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use MorphicAbstractEloquent\CollectionHelpers\EloquentCollectionHelpers;
use MorphicAbstractEloquent\Models\AbstractRuntimeModel;
use MorphicAbstractEloquent\Traits\RelationsSanitizingMethods;

class MorphicEloquentBuilder extends Builder
{
    use RelationsSanitizingMethods;

    protected string $morphColumnName;

       /**
     * Set a model instance for the model being queried.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return $this
     */
    public function setModel(Model $model)
    {
        if(!$model instanceof AbstractRuntimeModel)
        {
            new Exception("The model passed to spatie's eloquent builder : must be AbstractRuntimeModel typed model !");
        }

        parent::setModel($model);

        $this->setMorphColumnName( $model->getMorphColumnName() );

        return $this;
    }

    public function setMorphColumnName(string $morphColumnName) : self
    {
        $this->morphColumnName = $morphColumnName;
        return $this;
    }

    public function getMorphColumnName() : ?string
    {
        return $this->morphColumnName;
    }
        /**
     * Get the hydrated models without eager loading.
     *
     * @param  array|string  $columns
     * @return \Illuminate\Database\Eloquent\Model[]|static[]
     */
    public function getModels($columns = ['*'])
    {
        $models = parent::getModels();
        return EloquentCollectionHelpers::excludeArrayAbstractRuntimeModel($models);
    }

    /**
     * Eager load the relationships for the models.
     *
     * @param  array  $models
     * @return array
     */
    public function eagerLoadRelations(array $models)
    {
        if(empty($relations = $this->getEagerLoads()))
        {
            return $models;
        }

        $morphicModelsCollection = EloquentCollectionHelpers::initEloquentCollection($models);
        $morphGroupedCollections = EloquentCollectionHelpers::groupByMorphColumnValue($morphicModelsCollection , $this->getMorphColumnName());
        
        $morphGroupedCollections->each(function($subCollection , $morphValue)  use ($relations , $morphGroupedCollections)
        {
            $sanitizedModelTypeEagers =  $this->sanitizeMorpicTypeLoadableRelationships($relations , $subCollection->first() );

            if($morphValue && !empty($sanitizedModelTypeEagers))
            {
                 // eager loading for each morph type  
                $morphGroupedCollections->offsetSet($morphValue,  $this->loadEagerLoadSubCollectionRelations( $subCollection , $sanitizedModelTypeEagers ) );
            }

        });
        return  EloquentCollectionHelpers::getFlatEloquentCollection($morphGroupedCollections)->toArray(); 
         
    }

    protected function loadEagerLoadSubCollectionRelations(Collection $subCollection , $eagers) : Collection
    { 
        $models = $subCollection->all();
        foreach ($eagers as $name => $constraints) {
            // For nested eager loads we'll skip loading them here and they will be set as an
            // eager load on the query to retrieve the relation so that they will be eager
            // loaded on that query, because that is where they get hydrated as models.
            if (strpos($name, '.') === false) {
                
                $models = $this->eagerLoadRelation($models, $name, $constraints);
            }
        }

        return $subCollection->replace($models);;
    }

    // /**
    //  * Eagerly load the relationship on a set of models.
    //  *
    //  * @param  array  $models
    //  * @param  string  $name
    //  * @param  \Closure  $constraints
    //  * @return array
    //  */
    // protected function eagerLoadRelation(array $models, $name, Closure $constraints)
    // {
    //     // First we will "back up" the existing where conditions on the query so we can
    //     // add our eager constraints. Then we will merge the wheres that were on the
    //     // query back to it in order that any where conditions might be specified.
    //     $relation = $this->getRelation($name);

    //     $relation->addEagerConstraints($models);

    //     $constraints($relation);

    //     // Once we have the results, we just match those back up to their parent models
    //     // using the relationship instance. Then we just return the finished arrays
    //     // of models which have been eagerly hydrated and are readied for return.
    //     return $relation->match(
    //         $relation->initRelation($models, $name),
    //         $relation->getEager(), $name
    //     );
    // }

}