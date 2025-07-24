<?php

namespace MorphicAbstractEloquent\AbstractMorphingSpatieCustomization;


use MorphicAbstractEloquent\Models\AbstractRuntimeModel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Spatie\QueryBuilder\QueryBuilder;

class AbstractMorphingSpatieBuilder extends QueryBuilder
{ 
    
    protected ?Collection $totalAllowedFilters = null;

    public function __construct(
        protected EloquentBuilder|Relation $subject,
        ?Request $request = null
    ) {

        parent::__construct($subject , $request);

        $this->chcekSubjectValidity();

    }

    protected function chcekSubjectValidity(): self
    {
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
        return isset($this->allowedFilters) && $this->allowedFilters->isNotEmpty();
    }
     
    protected function addToTotalAllowedFilters(Collection $filters) : void
    {
        $this->totalAllowedFilters = $this->totalAllowedFilters?->merge($filters->all()) ?? $filters;
    }

    public function getAllowedFilters() : Collection
    {
        return $this->totalAllowedFilters ?? collect();
    }
    /**
     * @param mixed $filters
     * 
     * @return self
     */
    public function allowedFilters($filters): static
    {   
        parent::allowedFilters($filters);
        $this->addToTotalAllowedFilters($this->allowedFilters);
        return $this;
    }

    public function getMorphicRelationshipsFilters() : array
    {   
        return FilterRuntimeManager::Singleton()->getAllowedFilters();
    }

    public function applyDefinedMorphicFilters() : self
    {
        $filters = $this->getMorphicRelationshipsFilters(); 
        return $this->allowedFilters($filters); // the method in this class ... not th parent's method
    }
  
    protected function addFiltersToQuery()  :void
    {
        parent::addFiltersToQuery();
        FilterRuntimeManager::Singleton()->customizeQuery( $this->getEloquentBuilder() );
    }

}