<?php

namespace MorphicAbstractEloquent\AbstractMorphingSpatieCustomization;

use MorphicAbstractEloquent\AbstractMorphingSpatieCustomization\FilterIdentifiers\MorphicRelationshipFilterIdentifier;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;

class FilterRuntimeManager
{

    protected static ?FilterRuntimeManager $instance = null;
    protected array $queryIncludedTableFilterIdentifiers = [];
    protected array $QueryChainStarterTableFilterIdentifiers = [];
    protected array $registeredAllowedFilters = [];
    protected array $registeredAllowedFilterIdentifiers = [];
    protected array $requestedFilterIdentifiers = [];

    protected array $excludedTableFilterIdenfirers = [];

    private function __construct(){} // must be private

    public static function Singleton() : FilterRuntimeManager
    {
       if(!static::$instance)
        {
            static::$instance = new static();
        }
        return static::$instance;
    }
    public function filterIdentifierToAllowedFilter(MorphicRelationshipFilterIdentifier $filterIdentifier): AllowedFilter
    {
        return MorphicRelationshipAllowedFilterFactory::getAllowedFiltersForMorphicRelationship($filterIdentifier);
    }

    public static function composeFilterIdentifierKey(MorphicRelationshipFilterIdentifier $filterIdentifier) : string
    {
        return $filterIdentifier->getFilterRequestKey() ;
    }

    public function addMorphicAllowedFilter(MorphicRelationshipFilterIdentifier  $filterIdentifier , string $key) : self
    {
        $this->registeredAllowedFilters[$key] = $this->filterIdentifierToAllowedFilter($filterIdentifier) ;
        return $this;
    }
    public function addAllowedFilterIdentifier(MorphicRelationshipFilterIdentifier  $filterIdentifier , string $key) : self
    {
        $this->registeredAllowedFilterIdentifiers[$key] = $filterIdentifier;
        return $this;
    }

    /**
     * For registering and defining an AllowedFitler to pass it to SpatieBuilder later .... this filter is not requested from request yet ... the requested filters
     * will be handled when SpatieBuilder pass them to registerRequestedFilter method
     */
    public function registerMorphicAllowedFilter(MorphicRelationshipFilterIdentifier  $filterIdentifier)
    {
        $key = $this::composeFilterIdentifierKey($filterIdentifier) ;
        $this->addAllowedFilterIdentifier($filterIdentifier , $key);
        $this->addMorphicAllowedFilter( $filterIdentifier , $key);
    }

    public function registerAllowedFilter(AllowedFilter $allowedFilter)  : self
    {
        $this->registeredAllowedFilters[] = $allowedFilter;
        return $this;
    }

    public function registerAllowedFilters(array  $allowedFilters = []): self
    {
        foreach($allowedFilters as $allowedFilter)
        {
            if($allowedFilter instanceof AllowedFilter)
            {
                $this->registerAllowedFilter($allowedFilter);
            }
        }
        return $this;
    }
    protected function registerRequestedFilterIdentifier(MorphicRelationshipFilterIdentifier  $filterIdentifier)  : self
    {
        $filterKey = $this::composeFilterIdentifierKey($filterIdentifier) ;
        $this->requestedFilterIdentifiers[$filterKey] = $filterIdentifier;
        return $this;
    }
    function getRequestedFilterIdentifiers()  : array
    {
        return  $this->requestedFilterIdentifiers;
    }
    public function getAllowedFilters() : array
    {
        return $this->registeredAllowedFilters;
    }
    public function hasAllowedFilters() : bool
    {
        return !empty( $this->getAllowedFilters() );
    }
    public function getAllowedFilterIdentifiers() : array
    {
        return $this->registeredAllowedFilterIdentifiers;
    } 

    public function getExcludedTableFilterIdentifiers() : array
    {
        return $this->excludedTableFilterIdenfirers;
    }
    protected function excludeTableFilterIdentifier(MorphicRelationshipTableFilterIdentifier $tableFilterIdentifier , ?string $identiferKey = null) : void
    {
        if(!$identiferKey)
        {
            $identiferKey = static::composeTableFilterIdentifierKey($tableFilterIdentifier);
        }
        $this->excludedTableFilterIdenfirers[$identiferKey] = $tableFilterIdentifier;
    }

    public function isTableFilterIdentifierExcluded(string $identiferKey  ) : bool
    {
        return array_key_exists( $identiferKey , $this->getExcludedTableFilterIdentifiers() );
    }

    protected function checkRequestedFilter(MorphicRelationshipFilterIdentifier $filterIdentifier) : bool
    {
        $filterKey = $this::composeFilterIdentifierKey($filterIdentifier) ;
        return  array_key_exists( $filterKey , $this->getAllowedFilterIdentifiers() ) ;
    }

    public function registerRequestedFilter(MorphicRelationshipFilterIdentifier $filterIdentifier) : self
    { 
        if($this->checkRequestedFilter($filterIdentifier)) 
        {
            /**
             * after checking if it is allowed to filtering on .... we will register it to process all filters tables later (on requesting them from MorphicRelationshipFiltersQueryCustomizer)
             */
            $this->registerRequestedFilterIdentifier($filterIdentifier);
        }
 
        return $this;
    }

    public static function composeTableFilterIdentifierKey(MorphicRelationshipTableFilterIdentifier $tableFilterIdentifier) : string
    {
        return $tableFilterIdentifier->getRelationshipTable() . "_" . $tableFilterIdentifier->getForeignKey();
    }

    public function getQueryIncludedTableFilterIdentifiers() : array
    {
        return $this->queryIncludedTableFilterIdentifiers;
    }

    public function isItQueryChainStart(MorphicRelationshipTableFilterIdentifier $tableFilterIdentifier) : bool
    {
        return empty( $tableFilterIdentifier->getParentTableIdentifiers() ) ; // if it has not a parent ... it is the parent and the existing query will start from it 
    }

    protected function addQueryChainStarterTableFilterIdentifier(MorphicRelationshipTableFilterIdentifier $tableFilterIdentifier) : void
    {
        if($this->isItQueryChainStart($tableFilterIdentifier))
        {
            $this->QueryChainStarterTableFilterIdentifiers[ $this::composeTableFilterIdentifierKey($tableFilterIdentifier) ] =  $tableFilterIdentifier;
        }
    }
  
    public function isTableIncludedToQueryChain(MorphicRelationshipTableFilterIdentifier $tableFilterIdentifier) : bool
    { 
        $key = $this::composeTableFilterIdentifierKey($tableFilterIdentifier);
        return array_key_exists($key , $this->getQueryIncludedTableFilterIdentifiers());
    }


    protected function isTableFilterIdentifierValidToQueryIncluding(MorphicRelationshipTableFilterIdentifier $tableFilterIdentifier ) : bool
    {
        /***
         * 
         * 
         * stoping this code for now 
         * it is not enough and has unnecessary and very high cost 
         * because it is not enough to check if the tables have the filter key 
         * it also must check that the tables have the filtered relationships 
         */


        // $identiferKey = static::composeTableFilterIdentifierKey($tableFilterIdentifier);

        // // no need to processing ... it is already exclueded 
        // if($this->isTableFilterIdentifierExcluded($identiferKey))
        // {
        //     return false;
        // }

        // // need to check its requested filters
        // dd([$this->getRequestedFilterIdentifiers() , $tableFilterIdentifier->getRequestedFilterIdentifiers()]);
        // $differentsArray = array_diff_key( $this->getRequestedFilterIdentifiers() , $tableFilterIdentifier->getRequestedFilterIdentifiers() );

        // // has missed filter keys .... must not ignoring it and must not adding it to query
        // dd($differentsArray);
        // if(count($differentsArray) != 0)
        // {
        //     $this->excludeTableFilterIdentifier($tableFilterIdentifier , $identiferKey);
        //     return false;
        // }
        
        return true;
    }

    protected function includeTableFilterIdentifierToQuery(MorphicRelationshipTableFilterIdentifier $tableFilterIdentifier ) : void
    { 
        /**
         * we need to add on tables related to the requested filter 
         * +
         *  its parent tables will be used in nested  where exists clasuses
         * 
         * then will only return the parent table objects in getRelationshipTableFilterIdentifiers method
         */

        if($this->isTableFilterIdentifierValidToQueryIncluding($tableFilterIdentifier) && ! $this->isTableIncludedToQueryChain($tableFilterIdentifier))
        {
            $key = $this::composeTableFilterIdentifierKey($tableFilterIdentifier);

            $this->queryIncludedTableFilterIdentifiers[ $key ] = $tableFilterIdentifier;

            $this->addQueryChainStarterTableFilterIdentifier($tableFilterIdentifier); // if it is a query chain start point will be added  ... must hasn't have parent identifiers to be added

            foreach( $tableFilterIdentifier->getParentTableIdentifiers() as $key => $parentTableFilterIdentifier) 
            { 
                $this->includeTableFilterIdentifierToQuery($parentTableFilterIdentifier); // recall the same method once the $tableFilterIdentifier has parent table identifiers
            } 
            
        }
    }

    protected function processRequestedFilterQueryChain(MorphicRelationshipFilterIdentifier $filterIdentifier) : void
    { 
        foreach($filterIdentifier->getMorphicRelationshipTableFilterIdentifiers() as $tableFilterIdentifier)
        {
            $this->includeTableFilterIdentifierToQuery( $tableFilterIdentifier );
        }
    }

    protected function processRequestedFiltersQueryChain() : void
    { 
        foreach( $this->getRequestedFilterIdentifiers() as $filterKey => $filterIdentifier)
        {
            $this->processRequestedFilterQueryChain($filterIdentifier);
        } 
    }
    public function getQueryChainStarterTableFilterIdentifiers() : array
    { 
        return $this->QueryChainStarterTableFilterIdentifiers; 
        
    }

    public function customizeQuery(Builder $query)
    {
        $this->processRequestedFiltersQueryChain(); 
        MorphicRelationshipFiltersQueryCustomizer::customizeQuery($query , $this->getQueryChainStarterTableFilterIdentifiers() );
    }
}