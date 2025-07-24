<?php


namespace MorphicAbstractEloquent\AbstractMorphingSpatieCustomization\FilterIdentifiers;

use MorphicAbstractEloquent\AbstractMorphingSpatieCustomization\FilterRuntimeManager;
use MorphicAbstractEloquent\AbstractMorphingSpatieCustomization\MorphicRelationshipTableFilterIdentifier;
use Illuminate\Database\Eloquent\Builder;

abstract class MorphicRelationshipFilterIdentifier
{

    protected array $morphicRelationshipTableFilterIdentifiers = []; 
    protected array $morphicRelationshipTables = [];
    protected string $filterRequestKey ;
    protected ?string $filteringInternalColumn = null;
     
    abstract public function filterHasJustRequested(Builder $query, $value, string $property) : void;

    public function __construct(string $filterRequestKey ,  ?string $filteringInternalColumn = null)
    {
        $this->setFilterRequestKey($filterRequestKey)->setFilteringInternalColumn($filteringInternalColumn);
    }

    
    public function setFilteringInternalColumn(?string $filteringInternalColumn = null) : self
    {
        $this->filteringInternalColumn = $filteringInternalColumn ?? $this->getFilterRequestKey();
        return $this;
    }
    
    public function getFilteringInternalColumn() : string|null
    {
        return $this->filteringInternalColumn;
    }
     
 
    public function setFilterRequestKey(string $filterRequestKey) : self
    {
        $this->filterRequestKey = $filterRequestKey;
        return $this;
    }
    
    public function getFilterRequestKey() : string
    {
        return $this->filterRequestKey;
    }
    
   
    public function getMorphicRelationshipTableFilterIdentifiers():  array
    {
        return $this->morphicRelationshipTableFilterIdentifiers;
    }

    protected function registerSelfAllowedFilter() : void
    {
        FilterRuntimeManager::Singleton()->registerMorphicAllowedFilter($this);
    }

    public function filterOnRelationshipTable(MorphicRelationshipTableFilterIdentifier $tableFilterIdentifier) : self
    {
        $this->registerSelfAllowedFilter();

        $key = FilterRuntimeManager::composeTableFilterIdentifierKey($tableFilterIdentifier);

        $this->morphicRelationshipTableFilterIdentifiers[ $key ] = $tableFilterIdentifier;
        $this->morphicRelationshipTables[$key] = $tableFilterIdentifier->getRelationshipTable();

        return  $this;
    }

    public function filterOnRelationshipTables(array $morphicRelationshipTableFilterIdentifiers = []) : self
    {
        foreach($morphicRelationshipTableFilterIdentifiers as $identifer)
        { 
            if($identifer instanceof MorphicRelationshipTableFilterIdentifier)
            {
                $this->filterOnRelationshipTable($identifer);
            }
        }
        return  $this;
    }  

    public function getTableNames() : array
    {
        return $this->morphicRelationshipTables;
    }

    protected function registerRequestedFilter() : void
    { 
        FilterRuntimeManager::Singleton()->registerRequestedFilter( $this );
    } 

     
}