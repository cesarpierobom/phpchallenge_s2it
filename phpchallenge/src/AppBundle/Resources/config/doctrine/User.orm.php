<?php

use Doctrine\ORM\Mapping\ClassMetadataInfo;

$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
$metadata->customRepositoryClassName = 'AppBundle\Repository\UserRepository';
$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT);
$metadata->mapField(array(
   'fieldName' => 'id',
   'type' => 'integer',
   'id' => true,
   'columnName' => 'id',
  ));
$metadata->mapField(array(
   'columnName' => 'first_name',
   'fieldName' => 'firstName',
   'type' => 'string',
   'length' => '190',
   'nullable' => true,
  ));
$metadata->mapField(array(
   'columnName' => 'middle_name',
   'fieldName' => 'middleName',
   'type' => 'string',
   'length' => '190',
   'nullable' => true,
  ));
$metadata->mapField(array(
   'columnName' => 'last_name',
   'fieldName' => 'lastName',
   'type' => 'string',
   'length' => '190',
   'nullable' => true,
  ));
$metadata->mapField(array(
   'columnName' => 'login',
   'fieldName' => 'login',
   'type' => 'string',
   'length' => '190',
   'unique' => true,
  ));
$metadata->mapField(array(
   'columnName' => 'email',
   'fieldName' => 'email',
   'type' => 'string',
   'length' => '190',
   'unique' => true,
  ));
$metadata->mapField(array(
   'columnName' => 'status',
   'fieldName' => 'status',
   'type' => 'boolean',
  ));
$metadata->mapField(array(
   'columnName' => 'created_at',
   'fieldName' => 'createdAt',
   'type' => 'datetime',
   'nullable' => true,
  ));
$metadata->mapField(array(
   'columnName' => 'updated_at',
   'fieldName' => 'updatedAt',
   'type' => 'datetime',
   'nullable' => true,
  ));
$metadata->mapField(array(
   'columnName' => 'deleted_at',
   'fieldName' => 'deletedAt',
   'type' => 'datetime',
   'nullable' => true,
  ));
$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_AUTO);