<?php

namespace MorphicAbstractEloquent\RelationIdentifiers;

use MorphicAbstractEloquent\Models\AbstractRuntimeModel;
use Illuminate\Database\Eloquent\Model;

class MorphToSingleTableRelationIdentifier extends RelationIdentifier
{
    protected string $relationName;
    protected string $ownerKeyName ;
    protected string $morpColumnName ;
    protected string $foreignKeyName ;
    protected string $relatedAbstractTableName ;
    protected AbstractRuntimeModel $relatedAbstractModel;
    protected Model $childModel;
    protected bool $hasNullableMorphColumn = false;

    public function __construct(string $relationName , string $relatedAbstractTableName , string $foreignKeyName , string $morpColumnName , Model $childModel , $ownerKeyName = null )
    {
        $this->setRelationName($relationName);
        $this->setRelatedAbstractTableName($relatedAbstractTableName);
        $this->setForeignKeyName($foreignKeyName);
        $this->setMorpColumnName($morpColumnName);
        $this->setOwnerKeyName($ownerKeyName);
        $this->setChildModel($childModel);
        $this->initRelatedAbstractModel();
    }


    public function setRelationName(string $relationName) : self
    {
        $this->relationName = $relationName;
        return $this;
    }
    public function getRelationName() : string
    {
        return $this->relationName ;
    }

    protected function initRelatedAbstractModel() : void
    {
        $model =  (new AbstractRuntimeModel())->setTable(  $this->getRelatedAbstractTableName() )
                                              ->setMorphColumnName( $this->getMorphColumnName() );
        $this->relatedAbstractModel = $model;
    }

    public function getRelatedAbstractModel() : AbstractRuntimeModel
    {
        return $this->relatedAbstractModel ;
    }

    public function setRelatedAbstractTableName(string $relatedAbstractTableName) : self
    {
        $this->relatedAbstractTableName = $relatedAbstractTableName;
        return $this;
    }

    public function getRelatedAbstractTableName() : string
    {
        return $this->relatedAbstractTableName;
    }

    public function setForeignKeyName(string $foreignKeyName) : self
    {
        $this->foreignKeyName = $foreignKeyName;
        return $this;
    }

    public function getForeignKeyName() : string
    {
        return $this->foreignKeyName;
    }

    public function setMorpColumnName(string $morpColumnName  ) : self
    {
        $this->morpColumnName = $morpColumnName; 
        return $this;
    }

    public function getMorphColumnName() : string
    {
        return $this->morpColumnName;
    } 

    public function setChildModel(Model $childModel) : self
    {
        $this->childModel = $childModel;
        return $this;
    }
    
    public function getChildModel() : Model
    {
        return $this->childModel;
    }

    protected function setOwnerKeyName($ownerKeyName = null) : self
    {
        $this->ownerKeyName = $ownerKeyName ?? $this->relatedAbstractModel->getKeyName();
        return $this;
    }

    public function getOwnerKeyName() : string
    {
        return $this->ownerKeyName;
    }
}
