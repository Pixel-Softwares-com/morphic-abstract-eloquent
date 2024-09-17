<?php

namespace MorphicAbstractEloquent\AbstractMorphingSpatieCustomization;

use MorphicAbstractEloquent\AbstractMorphingSpatieCustomization\FilterIdentifiers\MorphicRelationshipFilterIdentifier;
use Illuminate\Database\Eloquent\Builder  ;
use Spatie\QueryBuilder\Filters\Filter;

class MorphicRelationshipFilterRequestingListener implements Filter
{

    protected MorphicRelationshipFilterIdentifier  $filterIdentifier ;
    public function __construct(MorphicRelationshipFilterIdentifier $filterIdentifier)
    {
        $this->filterIdentifier = $filterIdentifier;
    }
  
    public function __invoke(Builder $query, $value, string $property)
    {
        $this->filterIdentifier->filterHasJustRequested($query , $value , $property);
    }

}