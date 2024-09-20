<?php

namespace MorphicAbstractEloquent\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use MorphicAbstractEloquent\EloquentBuilder\MorphicEloquentBuilder;
use stdClass;

class AbstractRuntimeModel extends Model
{
 
    protected ?string $morphColumnName  = null;
    protected ?string $newInstanceInitializationClass = null;

        /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        static::unguard(); // because we use this model trustly from our custom classes ... only data come from our database will be fill
        parent::__construct($attributes );
        $this::reguard();
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

    public function checkRequiredMetaDataPassing() : void
    {
        if(!$this->getTable())
        {
            throw new Exception("No tablename is set for AbstractRuntimeModel object !");
        }

        if(!$this->getMorphColumnName())
        {
            throw new Exception("No MorphColumnName is set for AbstractRuntimeModel object !");
        }
    }
    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newEloquentBuilder($query)
    {
        return new MorphicEloquentBuilder($query);
    }
    /**
     * Get a new query builder that doesn't have any global scopes.
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newQueryWithoutScopes()
    {
        return $this->newModelQuery() // new eloquent builder instance
                    ->with($this->with); // we just need to pass with array to query builder
                    // ->withCount($this->withCount); stoping this until checking its behaviors 
    }

    protected function setNewInstanceInitializationClass(string $class) : void
    {
        $this->newInstanceInitializationClass = $class;
    }
    
    protected function getInitializableInstanceClass(array $attributes = [] ) : string
    {
        if($this->newInstanceInitializationClass )
        {
            return $this->newInstanceInitializationClass ;
        }
        if(empty($attributes))
        {
            return static::class;
        }

        return $this->getModelClassBasedOnMorpColumnValue($attributes);
    }
     
    /**
     * Create a new instance of the given model.
     * overriding the base merthod to get instance based on morphValue if it is exists
     *
     * @param  array  $attributes
     * @param  bool  $exists
     * @return static
     */
    public function newInstance($attributes = [], $exists = false)
    {
        // This method just provides a convenient way for us to generate fresh model
        // instances of this current model. It is particularly useful during the
        // hydration of new objects via the Eloquent query builder instances.
        $attributes = $this->parseAttributes($attributes);
        $modelClass = $this->getInitializableInstanceClass($attributes);
      
        $model = new $modelClass($attributes);

        $model->exists = $exists;

        $model->setConnection( $this->getConnectionName() );

        $model->setTable($this->getTable());

        if($model instanceof AbstractRuntimeModel)
        {
            $model->setMorphColumnName($this->getMorphColumnName());
        }
 
        $model->mergeCasts($this->casts);

        return $model;
    }

    /**
     * Create a new model instance that is existing.
     *
     * overriding the base merthod to get instance based on morphValue if it is exists
     * 
     * @param  array  $attributes
     * @param  string|null  $connection
     * @return static
     */
    public function newFromBuilder($attributes = [], $connection = null)
    { 
        $attributes = $this->parseAttributes($attributes);
        
        // because we don't pass the data to newInstance method ... we need to determine the needed initializabale model class before calling the method ,
        //will set $this->newInstanceInitializationClass value to a morphed model class from morph map ... or using the AbstractRuntimeModel class by default
        $this->setNewInstanceInitializationClass( $this->getModelClassBasedOnMorpColumnValue( $attributes ) );
        
        $model = $this->newInstance([], true); // empty attributes to fill them with no check or conditions with setRawAttributes (it is a parent behavior .. not our)

        $model->setRawAttributes( $attributes, true);

        if($connection)
        {
            $model->setConnection($connection);
        }
        
        $model->fireModelEvent('retrieved', false);

        return $model;
    }


    protected function parseAttributes(array | stdClass | Model $attributes = []) : array
    {

        if($attributes instanceof Model)
        {
          return $attributes->toArray();
        }
  
        if($attributes instanceof stdClass)
        {
           return (array) $attributes;
        }

        return $attributes;
    }

      
   /**
     * Create a new model instance by type.
     *
     * @param  ?string  $type
     * @return string|null
     */
    public function getModelClassFormMorphMap(?string $type) : string|null
    {
        $class = Model::getActualClassNameForMorph($type);
        return is_subclass_of($class , Model::class) ? $class : null;
    }
 

    protected function getAttributesMorphValue(array $attributes = []) : string|null
    { 
        $morphColumnName = $this->getMorphColumnName();  
        
        return $attributes[ $morphColumnName ] ?? null; 
    }
    protected function getModelClassBasedOnMorpColumnValue(array $attributes = []) : string
    { 
      $morphValue = $this->getAttributesMorphValue($attributes);
      return $this->getModelClassFormMorphMap($morphValue) ?? static::class;  
    }
}