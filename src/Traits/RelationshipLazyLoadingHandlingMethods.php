<?php

namespace MorphicAbstractEloquent\Traits;

use MorphicAbstractEloquent\CollectionHelpers\EloquentCollectionHelpers;
use MorphicAbstractEloquent\Models\AbstractRuntimeModel;
use Illuminate\Database\Eloquent\Model; 
use Illuminate\Database\Eloquent\Collection  as EloquentCollection;  
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;

trait RelationshipLazyLoadingHandlingMethods
{
    use RelationsSanitizingMethods;

    protected array $realtionsForLazyLoading = [];
 
    protected function emptyQueryBuilderEagerLoads() : void
    {
        $this->query->withOnly([]);
    }
    
    /**
     * These are the relationships passed by with method on the query
     * It must not be loaded until changing the data collection's stdClass items to the convenient model type based on morphColumnValue
     */
    protected function getEagerLoadsToLazyLoading() : array
    {
        return $this->query->getEagerLoads();    
    }

    protected function setRelationsForLazyLoading(array $relations = []) : void
    {
        $this->realtionsForLazyLoading = $relations;
    }

    protected function getRelationsForLazyLoading() : array
    {
      return $this->realtionsForLazyLoading;  
    }
    
    protected function delayRelationshipLoading() : void
    {
        $this->setRelationsForLazyLoading( $this->getEagerLoadsToLazyLoading() );
        $this->emptyQueryBuilderEagerLoads();
    } 

    public function morphicCollectionRelationshipsLazyLoading(EloquentCollection $collection , string $morphColumnName ) : EloquentCollection
    {
        $relations =  $this->getRelationsForLazyLoading();
        
        if(!empty($relations) && !Model::preventsLazyLoading() )
        {
            $collection = EloquentCollectionHelpers::groupByMorphColumnValue($collection , $morphColumnName);
            $collection->each(function($subCollection , $morphValue) use ($relations)
            {
                $relations =  $this->sanitizeMorpicTypeLoadableRelationships($relations , $subCollection->first() );
                if($morphValue && !empty($relations))
                {
                    $subCollection->load( $relations ); // lazy loading for each morph type  
                }
            });
            $collection = EloquentCollectionHelpers::getFlatEloquentCollection($collection);
        }
        return $collection;
    }

    public function morphicModelRelationshipsLazyLoading(Model $model   ) : Model
    {
        $relations = $this->sanitizeMorpicTypeLoadableRelationships($this->getRelationsForLazyLoading() , $model );

        if(!empty($relations) && !Model::preventsLazyLoading() && !$model instanceof AbstractRuntimeModel)
        {
            /**
             *  need to test the fix
             */
            $model->load( $relations ); 
        }
        return $model;
    }

    public function morphicPaginatorRelationshipsLazyLoading( $paginator ,  string $morphColumnName) : void
    {
        if($paginator instanceof AbstractPaginator || $paginator instanceof  AbstractCursorPaginator)
        {
            $modelCollection =  $paginator->getCollection();
            $modelCollection = $this->morphicCollectionRelationshipsLazyLoading($modelCollection , $morphColumnName);
            $paginator->setCollection($modelCollection);
        }
    } 

}