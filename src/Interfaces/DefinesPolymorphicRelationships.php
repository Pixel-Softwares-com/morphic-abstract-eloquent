<?php


namespace MorphicAbstractEloquent\Interfaces;


interface DefinesPolymorphicRelationships
{
    public function getPolymorphicRelationshipNames() : array;
}