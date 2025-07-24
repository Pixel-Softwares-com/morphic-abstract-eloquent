<?php


namespace MorphicAbstractEloquent\AbstractMorphingSpatieCustomization\FilterIdentifiers;

use Illuminate\Database\Eloquent\Builder;

class MorphicRelationshipColumnFilterIdentifier extends MorphicRelationshipFilterIdentifier
{
 
    protected string $operator = "=";
    protected mixed $filterRequestValue = null;
   
 
    public static function create(string $filterRequestKey ,  ?string $filteringInternalColumn = null) : self
    {
        return new static($filterRequestKey , $filteringInternalColumn);
    }
    
    public function setFilterRequestValue(string $filterRequestValue) : self
    {
        $this->filterRequestValue = $filterRequestValue;
        return $this;
    }
    
    public function getFilterRequestValue() : string
    {
        return $this->filterRequestValue;
    }


    public function setOperator(string $operator = "=") : self
    {
        $this->operator = $operator;
        return $this;
    }

    public function getOperaotr() : string
    {
        return $this->operator;
    }
   
    protected function notifyRelatedTableFilterIdentifiers() : void
    {
        /**
         * @var MorphicRelationshipTableFilterIdentifier $tableFilterIdentifer
         */
        foreach($this->getMorphicRelationshipTableFilterIdentifiers() as $tableFilterIdentifer)
        {
            $tableFilterIdentifer->FilterHasJustRequest( $this );
        }
    }

    public function filterHasJustRequested(Builder $query, $value, string $property) :void 
    {
        $this->setFilterRequestValue($value)->setFilteringInternalColumn($property);

        $this->notifyRelatedTableFilterIdentifiers();
        $this->registerRequestedFilter();        
    }
     
}