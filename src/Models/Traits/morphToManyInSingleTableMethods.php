<?php

namespace MorphicAbstractEloquent\Models\Traits;

use MorphicAbstractEloquent\RelationIdentifiers\MorphToManyInSingleTableRelationIdentifier;
use MorphicAbstractEloquent\Relations\MorphToManyInSingleTable;
use Illuminate\Database\Eloquent\Relations\Relation;
use Exception;


trait morphToManyInSingleTableMethods
{

    protected function newMorpToManyInSingleTable(MorphToManyInSingleTableRelationIdentifier $relationIdentifier) : Relation
    {
        return new MorphToManyInSingleTable($relationIdentifier);
    }
    
    protected function initMorphToManyInSingleTableRelationIdentifier(
                                                                            string $relationName ,
                                                                            string $pivotTableName , 
                                                                            string $relatedAbstractTableName , 
                                                                            string $morpColumnName , 
                                                                            string $currentModelPivotKeyName , 
                                                                            string $relatedAbstractPivotKeyName,
                                                                            string $relatedAbstractKeyName = null  , 
                                                                            string $currentModelKeyName = null
                                                                        ) : MorphToManyInSingleTableRelationIdentifier 
    {
        return new MorphToManyInSingleTableRelationIdentifier(
             $relationName ,
             $pivotTableName , 
             $relatedAbstractTableName , 
             $morpColumnName , 
             $currentModelPivotKeyName , 
             $relatedAbstractPivotKeyName,
            $this , 
            $relatedAbstractKeyName    , 
            $currentModelKeyName  
        );
    }

    protected function getBelongsToManyRelationName( $relation = null) : string 
    {
        if(!is_null($relation))
        {
            return $relation;
        }

        // If no relation name was given, we will use this debug backtrace to extract
        // the calling method's name and use that as the relationship name as most
        // of the time this will be what we desire to use for the relationships.
        if (!method_exists($this , 'guessBelongsToManyRelation')) 
        {
            throw new Exception("Relation name is not set while defining a MorphToManyInSingleTable relationship !");
        } 

        return $this->guessBelongsToManyRelation(); 
    }

    public function MorphToManyInSingleTable(string $relatedAbstractTableName ,  string $morpColumnName , string $pivotTableName , string $currentModelPivotKeyName , string $relatedAbstractPivotKeyName  ,  ?string $relatedAbstractKeyName = null, ?string $currentModelKeyName = null , ?string $relation = null) : Relation 
    { 
        $relation = $this->getBelongsToManyRelationName($relation);

        $MorpToSingleTableIdentifier = $this->initMorphToManyInSingleTableRelationIdentifier(   
                                                                                                    $relation ,
                                                                                                    $pivotTableName , 
                                                                                                    $relatedAbstractTableName , 
                                                                                                    $morpColumnName , 
                                                                                                    $currentModelPivotKeyName , 
                                                                                                    $relatedAbstractPivotKeyName,
                                                                                                    $relatedAbstractKeyName    , 
                                                                                                    $currentModelKeyName 
                                                                                                );
        return $this->newMorpToManyInSingleTable($MorpToSingleTableIdentifier); 
    }


}