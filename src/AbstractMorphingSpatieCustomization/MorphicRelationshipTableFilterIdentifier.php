<?php

namespace MorphicAbstractEloquent\AbstractMorphingSpatieCustomization;

use MorphicAbstractEloquent\AbstractMorphingSpatieCustomization\FilterIdentifiers\MorphicRelationshipExistingFilterIdentifier;
use MorphicAbstractEloquent\AbstractMorphingSpatieCustomization\FilterIdentifiers\MorphicRelationshipFilterIdentifier;

class MorphicRelationshipTableFilterIdentifier
{ 
    protected string $relationshipTable ;
    protected string $foreignKey ;
    protected string $parentLocalKey = "id";
    protected array $parentTableIdentifiers = [];
    protected array $childTableIdentifiers = []; 
    protected array $requestedFilterIdentifiers = [];  
    
    public static function createParentTableFilterIdentifier(string $relationshipTable , string $foreignKey , string $parentLocalKey = "id") : self
    {
        return new static( $relationshipTable , $parentLocalKey, $foreignKey  );
    }

    public static function createChildTableFilterIdentifier(string $relationshipTable , string $foreignKey , string $parentLocalKey = "id") : self
    {
        return new static( $relationshipTable , $foreignKey , $parentLocalKey  );
    }

    private function __construct( string $relationshipTable , string $foreignKey , string $parentLocalKey = "id")
    {
        $this->setRelationshipTable($relationshipTable)->setForeignKey($foreignKey)->setParentLocalKey($parentLocalKey);
    }

    public function setRelationshipTable(string $relationshipTable) : self
    {
        $this->relationshipTable = $relationshipTable;
        return $this;
    }

    public function getRelationshipTable() : string
    {
        return $this->relationshipTable ;
    }


    public function setForeignKey(string $foreignKey) : self
    {
        $this->foreignKey = $foreignKey;
        return $this;
    }

    public function getForeignKey() : string
    {
        return $this->foreignKey ;
    }


    public function setParentLocalKey(string $parentLocalKey = "id") : self
    {
        $this->parentLocalKey = $parentLocalKey;
        return $this;
    }

    public function getParentLocalKey() : string
    {
        return $this->parentLocalKey ;
    }

  
    public function canFilterOnColumn(MorphicRelationshipFilterIdentifier $filterIdentifier) : self
    {
        $filterIdentifier->filterOnRelationshipTable($this); 
        return $this;
    }

    public function canFilterOnColumns(array $filterIdentifiers = []) : self
    {
        foreach($filterIdentifiers as $filterIdentifier)
        {
            if($filterIdentifier instanceof MorphicRelationshipFilterIdentifier)
            {
                $this->canFilterOnColumn($filterIdentifier);
            }
        }

        return $this;
    }

    protected function initMorphicRelationshipExistingFilterIdentifier(string $filterRequestKey ) :  MorphicRelationshipExistingFilterIdentifier
    {
        return MorphicRelationshipExistingFilterIdentifier::create($filterRequestKey);
    }

    public function filterOnRelationshipExisting(MorphicRelationshipExistingFilterIdentifier $existingFilterIdentifier ) : self
    { 
        /**
         * the filter will be registered as an allowed filter automatically
         */
        $existingFilterIdentifier->filterOnRelationshipTable($this);
        return $this;
    }
 

    public function FilterHasJustRequest(MorphicRelationshipFilterIdentifier $filterIdentifier) : void
    {
        $this->requestedFilterIdentifiers[  FilterRuntimeManager::composeFilterIdentifierKey($filterIdentifier)  ] = $filterIdentifier;
    } 
    public function getRequestedFilterIdentifiers() : array
    {
        return $this->requestedFilterIdentifiers;
    }

    public function addParentTableIdentifier(MorphicRelationshipTableFilterIdentifier $parentTableIdentifier ) : self
    {
        $this->parentTableIdentifiers[ $parentTableIdentifier->getRelationshipTable() ] = $parentTableIdentifier;
        return  $this;
    } 
    public function getParentTableIdentifiers() : array
    {
        return $this->parentTableIdentifiers;
    }
    public function addChildTableIdentifier(MorphicRelationshipTableFilterIdentifier $childTableIdentifier ) : self
    {
        $this->childTableIdentifiers[ $childTableIdentifier->getRelationshipTable() ] = $childTableIdentifier;
        return  $this;
    }
    public function getChildTableIdentifiers() : array
    {
        return $this->childTableIdentifiers;
    }
    public function filterOnRelatedTable(MorphicRelationshipTableFilterIdentifier $relatedTableFilterIdentifier) : self
    {
        $relatedTableFilterIdentifier->addParentTableIdentifier($this);
        $this->addChildTableIdentifier($relatedTableFilterIdentifier);
        return $this;
      
    }
 
}