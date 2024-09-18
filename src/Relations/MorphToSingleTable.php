<?php

namespace MorphicAbstractEloquent\Relations;
 
use MorphicAbstractEloquent\Models\AbstractRuntimeModel;
use MorphicAbstractEloquent\RelationIdentifiers\MorphToSingleTableRelationIdentifier;
use MorphicAbstractEloquent\Traits\RelationshipLazyLoadingHandlingMethods;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder ;
use Illuminate\Database\Eloquent\Model;  


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
 * @var AbstractRuntimeModel $related
 */
class MorphToSingleTable extends BelongsTo
{
    use RelationshipLazyLoadingHandlingMethods;

    protected ?string $defaultRelatedModelClass = null;
    protected MorphToSingleTableRelationIdentifier $relationIdentifier;
   

     /**
     * Create a new belongs to relationship instance.
     *
     * @param  Builder  $query
     * @param  \Illuminate\Database\Eloquent\Model  $child
     * @param  string  $foreignKey
     * @param  string  $ownerKey
     * @param  string  $relationName
     * @return void
     */
    public function __construct( MorphToSingleTableRelationIdentifier $relationIdentifier )
    {
        $this->setRelationIdentifier( $relationIdentifier ); 
        parent::__construct( 
                                $this->getRelatedTableQuery(),
                                $relationIdentifier->getChildModel() , // the parent is the same child object in this class .... we didn't change this behaior found on the BeLongsTo class 
                                $relationIdentifier->getForeignKeyName(),  
                                $relationIdentifier->getOwnerKeyName(), 
                                $relationIdentifier->getRelationName()
                            );
 
    }
    
    /**
     * returning an Eloquent query builder that has the global scopes registered on the AbstractRuntimeModel + has the value of the $with array found on the AbstractRuntimeModel 
     */
    protected function getRelatedTableQuery() : Builder
    {
        return $this->getRelationIdentifier()->getRelatedAbstractModel()->newQuery(); 
    } 

    public function setRelationIdentifier(MorphToSingleTableRelationIdentifier $relationIdentifier) : void
    {
        $this->relationIdentifier = $relationIdentifier;
    }
    public function getRelationIdentifier() : MorphToSingleTableRelationIdentifier
    {
        return $this->relationIdentifier;
    } 

    protected function getMorphColumnName() : string
    {
        return $this->getRelationIdentifier()->getMorphColumnName();
    } 
 
    public function useDefaultRelatedModel(string $modelClass) : self
    {
        if( is_subclass_of($modelClass , Model::class ) )
        {
            $this->defaultRelatedModelClass = $modelClass;
        }
        return $this;
    }

    /**
     *  we will never use the related model as a default because it is AbstractRuntimeModel typed model
     *
     * @return \Illuminate\Database\Eloquent\Model | null
     */
    protected function newRelatedDefaultInstance()
    {
        if(!$this->defaultRelatedModelClass)
        {
            return null;
        }
        return new $this->defaultRelatedModelClass;
    }
    
    /**
     * Get the default value for this relation.
     * this implementation is the same found on the SupportsDefaultModels trait ... we just add an extra condition to use the default model  feature .. and changed the related model instance initialization method
     *
     * @param  \Illuminate\Database\Eloquent\Model  $parent
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    protected function getDefaultFor(Model $parent)
    {
        if (! $this->withDefault || !$this->defaultRelatedModelClass)
        {
            return null; // no default model request to be return .... or no default type defined to use it as a default model
        }

        if(! $instance = $this->newRelatedDefaultInstance()) // condition for more consistency if the model initialization failed for a reason
        {
            return null;
        }

        if (is_callable($this->withDefault))
        {
            return call_user_func($this->withDefault, $instance, $parent) ?: $instance;
        }

        if (is_array($this->withDefault))
        {
            $instance->forceFill($this->withDefault);
        }

        return $instance;
    }
 
}