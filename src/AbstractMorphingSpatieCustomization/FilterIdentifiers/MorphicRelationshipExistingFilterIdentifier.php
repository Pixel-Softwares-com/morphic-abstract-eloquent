<?php


namespace MorphicAbstractEloquent\AbstractMorphingSpatieCustomization\FilterIdentifiers;
 
use Illuminate\Database\Eloquent\Builder;

class MorphicRelationshipExistingFilterIdentifier extends MorphicRelationshipFilterIdentifier
{ 
  
    public function filterHasJustRequested(Builder $query, $value, string $property) : void
    { 
        $this->registerRequestedFilter();        
    }
     
    public static function create(string $filterRequestKey ) : self
    {
        return new static($filterRequestKey);
    }
    
}