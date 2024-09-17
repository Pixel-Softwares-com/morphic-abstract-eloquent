<?php

namespace MorphicAbstractEloquent\AbstractMorphingSpatieCustomization ;

use MorphicAbstractEloquent\AbstractMorphingSpatieCustomization\FilterIdentifiers\MorphicRelationshipFilterIdentifier;
use Spatie\QueryBuilder\AllowedFilter;

class MorphicRelationshipAllowedFilterFactory
{
 
    public static function getAllowedFiltersForMorphicRelationship( MorphicRelationshipFilterIdentifier $filterIdentifier ) : AllowedFilter
    { 
        return AllowedFilter::custom($filterIdentifier->getFilterRequestKey() , new MorphicRelationshipFilterRequestingListener($filterIdentifier) , $filterIdentifier->getFilteringInternalColumn());
    }
 
}