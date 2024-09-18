<?php

namespace MorphicAbstractEloquent\Traits;

use Illuminate\Database\Eloquent\Model;
use MorphicAbstractEloquent\Interfaces\DefinesPolymorphicRelationships;

trait RelationsSanitizingMethods
{
  
    public function sanitizeMorpicTypeLoadableRelationships(array $lazyLoadingRelationships , Model $model) : array
    {
        if(!$model instanceof DefinesPolymorphicRelationships)
        {
            return $lazyLoadingRelationships;    
        }
        
        $modelPolymorpicRelationships = $model->getPolymorphicRelationshipNames();
        $validRelationships = [];

         // loop is needed because $lazyLoadingRelationships array may has constraints , but $modelPolymorpicRelationships may not has constraints by default
        foreach($lazyLoadingRelationships as $relationship => $callback)
        {
            if( is_int($relationship)  )
            {
                $relationship = $callback;
                $callback = function($query){};
            }

            if( array_key_exists($relationship , $modelPolymorpicRelationships ) || in_array($relationship , $modelPolymorpicRelationships) )
            {
                $validRelationships[$relationship] = $callback;
            }
        }
        return  $validRelationships;
    }
}