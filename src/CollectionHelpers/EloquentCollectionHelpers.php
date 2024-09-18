<?php

namespace MorphicAbstractEloquent\CollectionHelpers;

use MorphicAbstractEloquent\Models\AbstractRuntimeModel;
use Illuminate\Support\Collection as stdClassCollection;
use Illuminate\Database\Eloquent\Collection  as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;

class EloquentCollectionHelpers
{
    public static $AllowedToRetrieveAbstractRuntimeModel = false;
    
    public static function isModelAllowedToRetrieve(Model $model): bool
    {
        return static::$AllowedToRetrieveAbstractRuntimeModel || !$model instanceof AbstractRuntimeModel;
    }

    public static function excludeArrayAbstractRuntimeModel(array $dataArray = []): array
    {
        return array_filter(
                                $dataArray ,
                                 fn($model) => $model instanceof Model && static::isModelAllowedToRetrieve($model)
                            );

    }

    public static function excludeAbstractRuntimeModel(EloquentCollection $collection): EloquentCollection
    {
        return $collection->filter(fn($model) => static::isModelAllowedToRetrieve($model)); 
    }

    public static function excludePaginatorAbstractRuntimeModels($paginator): void
    {
        if ($paginator instanceof AbstractPaginator || $paginator instanceof  AbstractCursorPaginator) 
        {
            $modelCollection =  $paginator->getCollection();
            $modelCollection =  static::excludeAbstractRuntimeModel($modelCollection);
            $paginator->setCollection($modelCollection);
        }
    }

    public static function getFlatEloquentCollection(EloquentCollection $collection): EloquentCollection
    {
        $items = [];

        foreach ($collection as $item) {
            if ($item instanceof stdClassCollection) 
            {
                $items = array_merge($items, $item->all());
                continue;
            }
            $items[] = $item;
        }

        return  static::initEloquentCollection($items);
    }

    public static function groupByMorphColumnValue(EloquentCollection $collection,  string $morphColumnName): EloquentCollection
    {
        return $collection->groupBy($morphColumnName);
    }

    public static function initEloquentCollection(array $data = []): EloquentCollection
    {
        return new EloquentCollection($data);
    }
    public static function convertToEloquentCollection(stdClassCollection $collection): EloquentCollection
    {
        return static::initEloquentCollection($collection->all());
    }
}