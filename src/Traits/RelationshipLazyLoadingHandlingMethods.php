<?php

namespace MorphicAbstractEloquent\Traits;

use MorphicAbstractEloquent\CollectionHelpers\EloquentCollectionHelpers;
use MorphicAbstractEloquent\Models\AbstractRuntimeModel;
use Illuminate\Database\Eloquent\Model; 
use Illuminate\Database\Eloquent\Collection  as EloquentCollection; 
use MorphicAbstractEloquent\Interfaces\DefinesPolymorphicRelationships;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;

trait RelationshipLazyLoadingHandlingMethods
{
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
 

    protected function sanitizeMorpicTypeLoadableRelationships(array $lazyLoadingRelationships , Model $model) : array
    {
        if(!$model instanceof DefinesPolymorphicRelationships)
        {
            return $lazyLoadingRelationships;    
        }
        
        $modelPolymorpicRelationships = $model->getPolymorphicRelationshipNames();
        $validRelationships = [];

         // loop is needed because $lazyLoadingRelationships array may has constraints , but $modelPolymorpicRelationships may not has constraints by default
        foreach($lazyLoadingRelationships as $relationship => $callback)
        {
            if( is_int($relationship)  )
            {
                $relationship = $callback;
                $callback = function($query){};
            }

            if( array_key_exists($relationship , $modelPolymorpicRelationships ) || in_array($relationship , $modelPolymorpicRelationships) )
            {
                $validRelationships[$relationship] = $callback;
            }
        }
        return  $validRelationships;
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
            $model->load( $model );
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