<?php


namespace MorphicAbstractEloquent\EloquentBuilder;


use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use MorphicAbstractEloquent\CollectionHelpers\EloquentCollectionHelpers;
use MorphicAbstractEloquent\Models\AbstractRuntimeModel;
use MorphicAbstractEloquent\Traits\RelationsSanitizingMethods;

class MorphicEloquentBuilder extends Builder
{
    use RelationsSanitizingMethods;

    protected ?Model $eagerLoadingTempModelPrototype = null;
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
        return  EloquentCollectionHelpers::getFlatEloquentCollection($morphGroupedCollections)->all(); 
         
    }

       /**
     * Get the $eagerLoadingTempModelPrototype model if it is set or the AbstractRuneTimeModel 
     *
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function getModel()
    {
        return $this->eagerLoadingTempModelPrototype ?? $this->model;
    }
    protected function setEagerLoadingTempModelPrototype(Model $model)
    {
        $this->eagerLoadingTempModelPrototype = $model;
    }
    protected function clearEagerLoadingTempModelPrototype()
    {
        $this->eagerLoadingTempModelPrototype = null;
    }

    protected function loadEagerLoadSubCollectionRelations(Collection $subCollection , $eagers) : Collection
    { 
        $this->setEagerLoadingTempModelPrototype($subCollection->first());
        $models = $subCollection->all();
        
        foreach ($eagers as $name => $constraints) {
            // For nested eager loads we'll skip loading them here and they will be set as an
            // eager load on the query to retrieve the relation so that they will be eager
            // loaded on that query, because that is where they get hydrated as models.
            if (strpos($name, '.') === false) {
                
                $models = $this->eagerLoadRelation($models, $name, $constraints);
            }
        }

        $this->clearEagerLoadingTempModelPrototype();

        return $subCollection->replace($models);;
    }
    
}