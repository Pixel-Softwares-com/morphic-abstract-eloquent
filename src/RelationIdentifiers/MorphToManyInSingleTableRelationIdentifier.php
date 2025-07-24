<?php

namespace MorphicAbstractEloquent\RelationIdentifiers;

use MorphicAbstractEloquent\Models\AbstractRuntimeModel;
use Illuminate\Database\Eloquent\Model;

class MorphToManyInSingleTableRelationIdentifier extends RelationIdentifier
{ 
    protected string $relationName;
    protected string $pivotTableName;
    protected string $relatedAbstractTableName ;
    protected string $relatedAbstractKeyName ;
    protected string $currentModelPivotKeyName;
    protected string $relatedAbstractPivotKeyName;
    protected string $currentModelKeyName ;
    protected string $morpColumnName ;
    protected AbstractRuntimeModel $relatedAbstractModel;
    protected Model $currentModel;
 
    public function __construct(
            string $relationName ,
            string $pivotTableName , 
            string $relatedAbstractTableName , 
            string $morpColumnName , 
            string $currentModelPivotKeyName , 
            string $relatedAbstractPivotKeyName,
            Model $currentModel , 
            ?string $relatedAbstractKeyName = null  , 
            ?string $currentModelKeyName = null
        )
    {
        $this->setRelationName($relationName);
        $this->setPivotTableName($pivotTableName);
        $this->setRelatedAbstractTableName($relatedAbstractTableName);
        $this->setMorpColumnName($morpColumnName);
        $this->initRelatedAbstractModel();
        $this->setCurrentModelPivotKeyName($currentModelPivotKeyName);
        $this->setRelatedAbstractPivotKeyName($relatedAbstractPivotKeyName);
        $this->setCurrentModel($currentModel);
        $this->setRelatedAbstractKeyName($relatedAbstractKeyName);
        $this->setCurrentModelKeyName($currentModelKeyName);
    }


    public function setRelationName(string $relationName) : void
    {
        $this->relationName = $relationName;
    }
    public function getRelationName() : string 
    {
        return $this->relationName ;
    }
    
    public function getPrivotTableName() : string
    {
        return $this->pivotTableName;
    }
    public function setPivotTableName(string $pivotTableName)
    {
        $this->pivotTableName = $pivotTableName;
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
    
    public function setRelatedAbstractTableName(string $relatedAbstractTableName) : void
    {
        $this->relatedAbstractTableName = $relatedAbstractTableName;
    }

    public function getRelatedAbstractTableName() : string
    {
        return $this->relatedAbstractTableName;
    }

    public function setCurrentModelPivotKeyName(string $currentModelPivotKeyName) : void
    {
        $this->currentModelPivotKeyName = $currentModelPivotKeyName;
    }

    public function getCurrentModelPivotKeyName() : string
    {
        return $this->currentModelPivotKeyName;
    }

    public function setRelatedAbstractPivotKeyName(string $relatedAbstractPivotKeyName) : void
    {
        $this->relatedAbstractPivotKeyName = $relatedAbstractPivotKeyName;
    }

    public function getRelatedAbstractPivotKeyName() : string
    {
        return $this->relatedAbstractPivotKeyName;
    }

    public function setMorpColumnName(string $morpColumnName) : void
    {
        $this->morpColumnName = $morpColumnName;
    }

    public function getMorphColumnName() : string
    {
        return $this->morpColumnName;
    }

    public function setCurrentModel(Model $currentModel) : void 
    {
        $this->currentModel = $currentModel;
    }
    public function getCurrentModel() : Model 
    {
        return $this->currentModel;
    }

    protected function setRelatedAbstractKeyName($relatedAbstractKeyName = null) : void
    {
        $this->relatedAbstractKeyName = $relatedAbstractKeyName ?? $this->relatedAbstractModel->getKeyName();
    }

    public function getRelatedAbstractKeyName() : string
    {
        return $this->relatedAbstractKeyName;
    }
    
    protected function setCurrentModelKeyName($currentModelKeyName = null) : void
    {
        $this->currentModelKeyName = $currentModelKeyName ?? $this->currentModel->getKeyName();
    }

    public function getCurrentModelKeyName() : string
    {
        return $this->currentModelKeyName;
    }
}