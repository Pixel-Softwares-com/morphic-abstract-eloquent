<?php
namespace MorphicAbstractEloquent\AbstractMorphingSpatieCustomization;
 
use Illuminate\Database\Eloquent\Builder; 
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Support\Facades\DB; 

class MorphicRelationshipFiltersQueryCustomizer
{
    protected array $categorizedFilters = [];

    protected static function addWhereExistsQueryGroup(Builder | BaseBuilder $query , array $existingQueries = []) : void
    {
        if(empty($existingQueries))
        {
            return ;
        }

        $query->where(function(Builder | BaseBuilder $q) use ($existingQueries )
        {
            foreach($existingQueries as $queryIndex => $existingQuery)
            {
                $boolean = $queryIndex == 0 ? 'and' : 'or';
                $q->addWhereExistsQuery($existingQuery ,  $boolean  , false  );
            }
           
          }); 
    }

    protected static function getQueryBuilderTableName(Builder | BaseBuilder $builder) : string
    {
        if($builder instanceof Builder)
        {
            return  $builder->getQuery()->from;
        } 
        return $builder->from;
    }

    protected static function addExistingCondition(Builder | BaseBuilder $parentQuery , BaseBuilder $tableQuery , MorphicRelationshipTableFilterIdentifier $tableFilterIdentifier) : void
    { 

        $parentTable = static::getQueryBuilderTableName($parentQuery);
        $childTable =  static::getQueryBuilderTableName($tableQuery);

        $parentLocalKey = $parentTable . "." . $tableFilterIdentifier->getParentLocalKey();
        $childForeignKey = $childTable . "." . $tableFilterIdentifier->getForeignKey();
  
        $tableQuery->whereColumn( $parentLocalKey , "=", $childForeignKey);
    }

    protected static function handleValueWildcard(string $operator , $value)
    {
        $operator =  strtolower( $operator );
        $wildcardOperators = ["like" , "not like"];

        if( in_array($operator  , $wildcardOperators) )
        {
            return "%" . strval($value) . "%";
        }

        return $value;
    }

    protected static function addFilteringColumnsConditions(BaseBuilder $query , MorphicRelationshipTableFilterIdentifier $tableFilterIdentifier) : void
    {
        /**
         * @var  MorphicRelationshipFilterIdentifier $filterIdentifier
         */
    
        foreach($tableFilterIdentifier->getRequestedFilterIdentifiers() as $key => $filterIdentifier)
        {
            $column = $filterIdentifier->getFilteringInternalColumn();
            $operator = $filterIdentifier->getOperaotr();
            $value = $filterIdentifier->getFilterRequestValue();

            if (is_array($value)) 
            {
                 $query->whereRaw( "$column In ( ? ) " ,  $value  );
                 continue;
            }

            $value = static::handleValueWildcard($operator , $value);
            $query->whereRaw("$column $operator ? " , [ $value ]);
        }
    }

    protected static function initTableBaseQuery(string $tableName) : BaseBuilder
    {
        return DB::table($tableName);
    }

    protected static function getTableExistingQuery(Builder | BaseBuilder  $query , MorphicRelationshipTableFilterIdentifier $tableFilterIdentifier ) : BaseBuilder
    {
        $tableName = $tableFilterIdentifier->getRelationshipTable();

        $tableQuery = static::initTableBaseQuery($tableName);
        static::addFilteringColumnsConditions($tableQuery , $tableFilterIdentifier);
        static::addExistingCondition($query , $tableQuery , $tableFilterIdentifier);
        
        static::customizeQuery($tableQuery , $tableFilterIdentifier->getChildTableIdentifiers() );
          
        return $tableQuery;
    } 


    protected static function getRelationshipFiltersQueryArray(Builder | BaseBuilder $query , array $relationshipTableFilterIdentifiers) : array
    {
        $existingQueries = [];
        foreach($relationshipTableFilterIdentifiers as  $tableFilterIdentifier)
        {
            $existingQueries[] = static::getTableExistingQuery($query , $tableFilterIdentifier );
        }
        return $existingQueries;
    }

    public static function isTableIncludedToQuery(MorphicRelationshipTableFilterIdentifier $tableFilterIdentifier) : bool
    {
        return FilterRuntimeManager::Singleton()->isTableIncludedToQueryChain($tableFilterIdentifier);
    }

    protected static function sanitizeMorphicRelationshipTableFilterIdentifiersArray( array $relationshipTableFilterIdentifiers = []) : array
    { 
        return array_filter($relationshipTableFilterIdentifiers , function($filterIdentifier)
        {
            
            return  $filterIdentifier instanceof MorphicRelationshipTableFilterIdentifier
                    &&
                    static::isTableIncludedToQuery($filterIdentifier);
            
        });
    }

    public static function customizeQuery(Builder | BaseBuilder $query , array $relationshipTableFilterIdentifiers = []) : void
    {
        $relationshipTableFilterIdentifiers = static::sanitizeMorphicRelationshipTableFilterIdentifiersArray($relationshipTableFilterIdentifiers);

        if(empty($relationshipTableFilterIdentifiers)) { return; } // stoping code if it is empty

        $existingQueries = static::getRelationshipFiltersQueryArray($query ,$relationshipTableFilterIdentifiers);
        static::addWhereExistsQueryGroup($query , $existingQueries);
    
    } 
 
}