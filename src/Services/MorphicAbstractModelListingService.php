<?php

namespace MorphicAbstractEloquent\Services;


use MorphicAbstractEloquent\AbstractMorphingSpatieCustomization\AbstractMorphingSpatieBuilder;
use MorphicAbstractEloquent\Models\AbstractRuntimeModel;
use MorphicAbstractEloquent\Traits\RelationshipLazyLoadingHandlingMethods;
use Illuminate\Database\Eloquent\Builder; 
use Illuminate\Support\Traits\ForwardsCalls;

class MorphicAbstractModelListingService
{
    use RelationshipLazyLoadingHandlingMethods , ForwardsCalls;

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

    public function __call($name, $arguments)
    {  
        return $this->forwardDecoratedCallTo($this->query, $name, $arguments); 
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
  
}