<?php

namespace MorphicAbstractEloquent\Relations;

use MorphicAbstractEloquent\CollectionHelpers\EloquentCollectionHelpers;
use MorphicAbstractEloquent\Models\AbstractRuntimeModel;
use MorphicAbstractEloquent\RelationIdentifiers\MorphToManyInSingleTableRelationIdentifier;
use MorphicAbstractEloquent\Traits\RelationshipLazyLoadingHandlingMethods;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model; 
use Illuminate\Database\Eloquent\Collection  as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;

/**
 * The Algorithm of work :
 * 
 *
 * we need to get an instance of Eloquent query builder that has the global scopes registered on the model (in general case there is no general scopes on AbstractRuntimeModel but on inheritance it can be added to the custom provided model)
 * , the builder also must has the value of the $with array found on the model + the relationships will be requested to eagerloading (( at the the runtime : not at this point in this code ))
 * =======> 
 * 
 * Then at on calling these methods 'getResults' and 'get' we will get results on the base query  (Database query builder found as an attribute on the Eloquent query builder)
 * 
 * why ?
 * 
 * to avoid any initilaization of models from the AbstractRuntimeModel automatically and to get results speedly .
 * 
 * 
 * the result will be an Collection of  stdClass typed objects ... these objects must has the morpColumn + its value to allow us to replace the object with the convenient model types based on morph map defined in AppServiceProvider
 * 
 * 
 * the results collection will be grouped by the morphing column value to get a collection of sub collections which each collection is represent an collection of a specific type of models 
 * now we can load the relationships requested to be eagerloaded by lazy loading on the model collection 
 * and in the end we will return a collection contains the models of the whole sub collections  
 *   
 */

/**
 * @var EloquentBuilder $query
 * @var AbstractRuntimeModel $related
 */
class MorphToManyInSingleTable extends BelongsToMany
{
    use RelationshipLazyLoadingHandlingMethods;

    protected MorphToManyInSingleTableRelationIdentifier $relationIdentifier;
    protected array $lazyLoadingRelationships = [];
   

     /**
     * Create a new belongs to relationship instance.
     *
     * @param  EloquentBuilder  $query
     * @param  \Illuminate\Database\Eloquent\Model  $child
     * @param  string  $foreignKey
     * @param  string  $ownerKey
     * @param  string  $relationName
     * @return void
     */
    public function __construct( MorphToManyInSingleTableRelationIdentifier  $relationIdentifier )
    { 
        $this->setRelationIdentifier( $relationIdentifier ); 
        parent::__construct( 
                                $this->getRelatedTableQuery(),
                                $relationIdentifier->getCurrentModel() , // the parent is the same child object in this class .... we didn't change this behaior found on the BeLongsTo class 
                                $relationIdentifier->getPrivotTableName(),
                                $relationIdentifier->getCurrentModelPivotKeyName(),
                                $relationIdentifier->getRelatedAbstractPivotKeyName(),
                                $relationIdentifier->getCurrentModelKeyName(),  
                                $relationIdentifier->getRelatedAbstractKeyName(), 
                                $relationIdentifier->getRelationName()
                            );
    }
    
    /**
     * returning an Eloquent query builder that has the global scopes registered on the AbstractRuntimeModel + has the value of the $with array found on the AbstractRuntimeModel 
     */
    protected function getRelatedTableQuery() : EloquentBuilder
    {
        return $this->getRelationIdentifier()->getRelatedAbstractModel()->newQuery(); 
    }
 
    public function setRelationIdentifier(MorphToManyInSingleTableRelationIdentifier $relationIdentifier) : void
    {
        $this->relationIdentifier = $relationIdentifier;
    }
    public function getRelationIdentifier() : MorphToManyInSingleTableRelationIdentifier
    {
        return $this->relationIdentifier;
    }
 

    public function getMorphColumnName() : string
    {
        return $this->getRelationIdentifier()->getMorphColumnName();
    }
  
 
    protected function prepareToExecuteQuery() : void
    {
        $this->delayRelationshipLoading(); // and othe methods if it is needed   
    }
    /**
     * Execute the query as a "select" statement. 
     * (it is called when the getEager method called ... it is generally called in the EloquentBuilder during the eagerLoading of loaded models relationships)
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function get($columns = ['*'])
    {  

        $this->prepareToExecuteQuery();
         
        $modelCollection = parent::get($columns);

        $modelCollection = EloquentCollectionHelpers::excludeAbstractRuntimeModel($modelCollection);
        
        $this->morphicCollectionRelationshipsLazyLoading($modelCollection , $this->getMorphColumnName());

        return  $modelCollection;  
        
    }



    public function findOrNew($id, $columns = ['*'])
    {
        /**
         * no new empty AbstractRuntimeModel instance allowed to be return .... if no model is found null will be return
         */
        return $this->find($id , $columns);
    }
 
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    { 
        $this->prepareToExecuteQuery();

        $paginator = parent::paginate($perPage  , $columns  , $pageName , $page ); 

        EloquentCollectionHelpers::excludePaginatorAbstractRuntimeModels($paginator);

        $this->morphicPaginatorRelationshipsLazyLoading($paginator , $this->getMorphColumnName());
    
        return  $paginator;

    }
    public function simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $this->prepareToExecuteQuery();
        $paginator = parent::simplePaginate($perPage  , $columns  , $pageName  , $page  );
        
        
        EloquentCollectionHelpers::excludePaginatorAbstractRuntimeModels($paginator);
        
        $this->morphicPaginatorRelationshipsLazyLoading($paginator , $this->getMorphColumnName());
     
        return  $paginator;
    }

    public function cursorPaginate($perPage = null, $columns = ['*'], $cursorName = 'cursor', $cursor = null)
    {
        $this->prepareToExecuteQuery();
        $paginator = parent::cursorPaginate($perPage , $columns , $cursorName , $cursor );
     
        EloquentCollectionHelpers::excludePaginatorAbstractRuntimeModels($paginator);
        
        $this->morphicPaginatorRelationshipsLazyLoading($paginator , $this->getMorphColumnName());

        return  $paginator;
    }
}