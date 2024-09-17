<?php

namespace MorphicAbstractEloquent\Services;

use MorphicAbstractEloquent\AbstractMorphingSpatieCustomization\AbstractMorphingSpatieBuilder;
use MorphicAbstractEloquent\CollectionHelpers\EloquentCollectionHelpers;
use MorphicAbstractEloquent\Models\AbstractRuntimeModel;
use MorphicAbstractEloquent\Traits\RelationshipLazyLoadingHandlingMethods;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;

class MorphicAbstractModelListingService
{
    use RelationshipLazyLoadingHandlingMethods;

    protected string $tableName;
    protected string $morphColumnName;
    protected AbstractMorphingSpatieBuilder $query;
    protected AbstractRuntimeModel $abstractRuntimeModel;
    protected $BuilderQueryCallback = null;
    protected array $realtionsForLazyLoading = [];

    public function __construct(string $tableName, string $morphColumnName)
    {
        $this->setTableName($tableName)
            ->setMorphColumnName($morphColumnName)
            ->initAbstractRuntimeModel()
            ->initSpatieQueryBuilder();
    }

    protected function initAbstractRuntimeModel(): self
    {
        $this->abstractRuntimeModel = (new AbstractRuntimeModel())->setTable($this->tableName)->setMorphColumnName($this->morphColumnName);
        return $this;
    }

    protected function initAbstractRuntimeEloquentBuilder(): Builder
    {
        return $this->abstractRuntimeModel->newQuery();
    }
    protected function initSpatieQueryBuilder(): self
    {
        $this->query =  AbstractMorphingSpatieBuilder::for($this->initAbstractRuntimeEloquentBuilder());
        return $this;
    }

    public function callbackOnBuilderBeforeGettingResults($callback)
    {
        if (is_callable($callback)) {
            $this->BuilderQueryCallback = $callback;
        }
    }

    public function setTableName(string $tableName): self
    {
        $this->tableName = $tableName;
        return $this;
    }
    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function setMorphColumnName(string $morphColumnName): self
    {
        $this->morphColumnName = $morphColumnName;
        return $this;
    }

    public function getMorphColumnName(): string
    {
        return $this->morphColumnName;
    }
 
    protected function prepareToExecuteQuery(): void
    {
        $this->delayRelationshipLoading(); // and othe methods if it is needed   
    }

    protected function executeQueryBuilderCallback(): void
    {
        ($this->BuilderQueryCallback)($this->query);
    }


    public function get(): EloquentCollection
    {
        $this->executeQueryBuilderCallback();
        $this->prepareToExecuteQuery();
        $modelCollection = $this->query->get();
        EloquentCollectionHelpers::excludeAbstractRuntimeModel($modelCollection);
        return $this->morphicCollectionRelationshipsLazyLoading($modelCollection, $this->getMorphColumnName());
    }

    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null): Paginator
    {
        $this->executeQueryBuilderCallback();
        $this->prepareToExecuteQuery();

        $paginator = $this->query->paginate($perPage, $columns, $pageName, $page);

        EloquentCollectionHelpers::excludePaginatorAbstractRuntimeModels($paginator);

        $this->morphicPaginatorRelationshipsLazyLoading($paginator, $this->getMorphColumnName());
        return $paginator;
    }

    public function simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $this->executeQueryBuilderCallback();
        $this->prepareToExecuteQuery();

        $paginator = $this->query->simplePaginate($perPage, $columns, $pageName, $page);

        EloquentCollectionHelpers::excludePaginatorAbstractRuntimeModels($paginator);

        $this->morphicPaginatorRelationshipsLazyLoading($paginator, $this->getMorphColumnName());
        return $paginator;
    }

    public function cursorPaginate($perPage = null, $columns = ['*'], $cursorName = 'cursor', $cursor = null)
    {
        $this->executeQueryBuilderCallback();
        $this->prepareToExecuteQuery();

        $paginator = $this->query->cursorPaginate($perPage, $columns, $cursorName, $cursor);

        EloquentCollectionHelpers::excludePaginatorAbstractRuntimeModels($paginator);

        $this->morphicPaginatorRelationshipsLazyLoading($paginator, $this->getMorphColumnName());
        return $paginator;
    }
}