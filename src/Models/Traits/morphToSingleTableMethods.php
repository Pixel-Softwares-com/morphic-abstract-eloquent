<?php

namespace MorphicAbstractEloquent\Models\Traits;

use MorphicAbstractEloquent\RelationIdentifiers\MorphToSingleTableRelationIdentifier;
use MorphicAbstractEloquent\Relations\MorphToSingleTable;
use Illuminate\Database\Eloquent\Relations\Relation;
use Exception;

trait morphToSingleTableMethods
{

    protected function newMorpToSingleTable(MorphToSingleTableRelationIdentifier $relationIdentifier): Relation
    {
        return new MorphToSingleTable($relationIdentifier);
    }

    protected function initMorpToSingleTableIdentifier(string $relationName, string $relatedTableName, string $foreignKeyName, string $morpColumnName, $ownerKey = null): MorphToSingleTableRelationIdentifier
    {
        return new MorphToSingleTableRelationIdentifier($relationName,  $relatedTableName,   $foreignKeyName,   $morpColumnName, $this, $ownerKey);
    }

    //TODO: fix typo
    protected function getBelongsTpRelationName($relation = null): string
    {
        if (!is_null($relation)) {
            return $relation;
        }

        // If no relation name was given, we will use this debug backtrace to extract
        // the calling method's name and use that as the relationship name as most
        // of the time this will be what we desire to use for the relationships.
        if (!method_exists($this, 'guessBelongsToRelation')) {
            throw new Exception("Relation name is not set while defining a morpToSingleTable relationship !");
        }

        return  $this->guessBelongsToRelation();
    }

    public function morpToSingleTable(string $relatedTableName, string $foreignKeyName, string $morpColumnName, $ownerKey = null, $relation = null): Relation
    {
        $relation = $this->getBelongsTpRelationName($relation);
        $MorpToSingleTableIdentifier = $this->initMorpToSingleTableIdentifier($relation,   $relatedTableName,   $foreignKeyName,   $morpColumnName, $ownerKey);
        return $this->newMorpToSingleTable($MorpToSingleTableIdentifier);
    }
}
