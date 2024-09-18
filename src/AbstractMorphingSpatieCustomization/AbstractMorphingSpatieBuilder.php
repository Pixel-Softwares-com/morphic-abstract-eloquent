<?php

namespace MorphicAbstractEloquent\AbstractMorphingSpatieCustomization;

use MorphicAbstractEloquent\Models\AbstractRuntimeModel;
use Exception;
use Spatie\QueryBuilder\QueryBuilder;

class AbstractMorphingSpatieBuilder extends QueryBuilder
{ 
    protected function initializeSubject($subject): self
    {
        parent::initializeSubject($subject);

        $eloquentBuilderModel = $this->getSubject()->getModel();

        if(!$eloquentBuilderModel instanceof AbstractRuntimeModel)
        {
            new Exception("The model passed to spatie's eloquent builder : must be AbstractRuntimeModel typed model !");
        } 
          
        $eloquentBuilderModel->checkRequiredMetaDataPassing();

        return $this;
    } 

    public function hasAllowedFilters() : bool
    {
        return (bool) $this->allowedFilters;
    }
   

    protected function mergeMorphicRelationshipAllowedFilters(array $filters = [])
    {
        return array_merge( $filters ,   FilterRuntimeManager::Singleton()->getAllowedFilters() );
    }
    /**
     * @param mixed $filters
     * 
     * @return self
     */
    public function allowedFilters($filters): self
    { 
        $filters = $this->mergeMorphicRelationshipAllowedFilters($filters);
        return parent::allowedFilters($filters);
    }



    protected function addFiltersToQuery()
    {
        parent::addFiltersToQuery();
        FilterRuntimeManager::Singleton()->customizeQuery( $this->getEloquentBuilder() );
    }
}