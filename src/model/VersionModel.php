<?php

final class VersionModel {
  
  private $key;
  private $googleID;
  private $packageName;
  private $platformName;
  private $versionName;
  private $hasFile = false;
  
  const KIND = 'version';
  
  public function __construct() {
    $this->datastore = id(new GoogleService())->getGoogleCloudDatastore();
  }
  
  public function getKey() {
    return $this->key;
  }
  
  public function setKey($key) {
    $this->key = $key;
    return $this;
  }
  
  public function getGoogleID() {
    return $this->googleID;
  }
  
  public function setGoogleID($googleID) {
    $this->googleID = $googleID;
    return $this;
  }
  
  public function getPackageName() {
    return $this->packageName;
  }
  
  public function setPackageName($name) {
    $this->packageName = $name;
    return $this;
  }
  
  public function getVersionName() {
    return $this->versionName;
  }
  
  public function setVersionName($name) {
    $this->versionName = $name;
    return $this;
  }
  
  public function getPlatformName() {
    return $this->platformName;
  }
  
  public function setPlatformName($name) {
    $this->platformName = $name;
    return $this;
  }
  
  public function getHasFile() {
    return $this->hasFile;
  }
  
  public function setHasFile($has_file) {
    $this->hasFile = $has_file;
    return $this;
  }
  
  private function mapProperties() {
    $mappings = array(
      'googleID' => $this->getGoogleID(),
      'packageName' => $this->getPackageName(),
      'platformName' => $this->getPlatformName(),
      'versionName' => $this->getVersionName(),
      'hasFile' => $this->getHasFile(),
    );
    
    $indexes = array(
      'googleID' => true,
      'platformName' => true,
      'packageName' => true,
      'versionName' => true,
    );
    
    $results = array();
    
    foreach ($mappings as $name => $value) {
      $prop = new Google_Service_Datastore_Property();
      if ($name === 'hasFile') {
        $prop->setBooleanValue($value);
      } else {
        $prop->setStringValue($value);
      }
      $prop->setIndexed(array_key_exists($name, $indexes));
      
      $results[$name] = $prop;
    }
    
    return $results;
  }
  
  private static function unmapProperties($entity, $model) {
    $props = $entity->getProperties();
    
    $props_googleID = idx($props, 'googleID');
    $props_packageName = idx($props, 'packageName');
    $props_platformName = idx($props, 'platformName');
    $props_versionName = idx($props, 'versionName');
    $props_hasFile = idx($props, 'hasFile');
    
    $value_googleID = null;
    $value_packageName = null;
    $value_platformName = null;
    $value_versionName = null;
    $value_hasFile = false;
    
    if ($props_googleID !== null) {
      $value_googleID = $props_googleID->getStringValue();
    }
    
    if ($props_packageName !== null) {
      $value_packageName = $props_packageName->getStringValue();
    }
    
    if ($props_platformName !== null) {
      $value_platformName = $props_platformName->getStringValue();
    }
    
    if ($props_versionName !== null) {
      $value_versionName = $props_versionName->getStringValue();
    }
    
    if ($props_hasFile !== null) {
      $value_hasFile = $props_hasFile->getBooleanValue();
    }
    
    $model
      ->setKey(head($entity->getKey()->getPath())->getId())
      ->setGoogleID($value_googleID)
      ->setPackageName($value_packageName)
      ->setPlatformName($value_platformName)
      ->setVersionName($value_versionName)
      ->setHasFile($value_hasFile);
      
    return $model;
  }
  
  public function create() {
    $path = new Google_Service_Datastore_KeyPathElement();
    $path->setKind(self::KIND);
    $path->setId(null);

    $key = new Google_Service_Datastore_Key();
    $key->setPath(array($path));
    
    $entity = new Google_Service_Datastore_Entity();
    $entity->setKey($key);
    $entity->setProperties($this->mapProperties());

    $mutation = new Google_Service_Datastore_Mutation();
    $mutation->setInsertAutoId(array($entity));
    $req = new Google_Service_Datastore_CommitRequest();
    $req->setMode('NON_TRANSACTIONAL');
    $req->setMutation($mutation);
    
    $dataset = $this->datastore->datasets;
    $dataset_id = "protobuild-index";
    
    $result = $dataset->commit($dataset_id, $req);
    
    $mutation = $result->getMutationResult();
    $insertedIds = $mutation->getInsertAutoIdKeys();
    
    $this->setKey(head(head($insertedIds)->getPath())->getId());
    return $this;
  }
  
  public function update() {
    $path = new Google_Service_Datastore_KeyPathElement();
    $path->setKind(self::KIND);
    $path->setId($this->getKey());

    $key = new Google_Service_Datastore_Key();
    $key->setPath(array($path));
    
    $entity = new Google_Service_Datastore_Entity();
    $entity->setKey($key);
    $entity->setProperties($this->mapProperties());

    $mutation = new Google_Service_Datastore_Mutation();
    $mutation->setUpdate(array($entity));
    $req = new Google_Service_Datastore_CommitRequest();
    $req->setMode('NON_TRANSACTIONAL');
    $req->setMutation($mutation);
    
    $dataset = $this->datastore->datasets;
    $dataset_id = "protobuild-index";
    
    $dataset->commit($dataset_id, $req);
  }
  
  public function delete() {
    $path = new Google_Service_Datastore_KeyPathElement();
    $path->setKind(self::KIND);
    $path->setId($this->getKey());

    $key = new Google_Service_Datastore_Key();
    $key->setPath(array($path));

    $mutation = new Google_Service_Datastore_Mutation();
    $mutation->setDelete(array($key));
    $req = new Google_Service_Datastore_CommitRequest();
    $req->setMode('NON_TRANSACTIONAL');
    $req->setMutation($mutation);
    
    $dataset = $this->datastore->datasets;
    $dataset_id = "protobuild-index";
    
    $dataset->commit($dataset_id, $req);
  }
  
  public function loadAllForPackage(
    GoogleToUserMappingModel $user,
    PackageModel $package) {
    
    $id_value = new Google_Service_Datastore_Value();
    $id_value->setStringValue($user->getGoogleID());
    
    $id_arg = new Google_Service_Datastore_GqlQueryArg();
    $id_arg->setName('id');
    $id_arg->setValue($id_value);
    
    $name_value = new Google_Service_Datastore_Value();
    $name_value->setStringValue($package->getName());
    
    $name_arg = new Google_Service_Datastore_GqlQueryArg();
    $name_arg->setName('name');
    $name_arg->setValue($name_value);
    
    $gql_query = new Google_Service_Datastore_GqlQuery();
    $gql_query->setQueryString('SELECT * FROM version WHERE googleID = @id AND packageName = @name');
    $gql_query->setNameArgs(array($id_arg, $name_arg));
    
    $query = new Google_Service_Datastore_RunQueryRequest();
    $query->setGqlQuery($gql_query);
    
    $dataset = $this->datastore->datasets;
    $dataset_id = "protobuild-index";
    
    $result = $dataset->runQuery($dataset_id, $query);
    
    $batch = $result->getBatch();
    $entities = $batch->getEntityResults();
    
    $results = array();
    
    foreach ($entities as $entity_result) {
      $entity = $entity_result->getEntity();
      
      $results[] = self::unmapProperties(
        $entity,
        id(new VersionModel()));
    }
    
    return $results;
  }
  
  public function loadByPackagePlatformAndVersion(
    GoogleToUserMappingModel $user,
    PackageModel $package,
    $platform,
    $version) {
    
    $id_value = new Google_Service_Datastore_Value();
    $id_value->setStringValue($user->getGoogleID());
    
    $id_arg = new Google_Service_Datastore_GqlQueryArg();
    $id_arg->setName('id');
    $id_arg->setValue($id_value);
    
    $name_value = new Google_Service_Datastore_Value();
    $name_value->setStringValue($package->getName());
    
    $name_arg = new Google_Service_Datastore_GqlQueryArg();
    $name_arg->setName('name');
    $name_arg->setValue($name_value);
    
    $platform_value = new Google_Service_Datastore_Value();
    $platform_value->setStringValue($platform);
    
    $platform_arg = new Google_Service_Datastore_GqlQueryArg();
    $platform_arg->setName('platform');
    $platform_arg->setValue($platform_value);
    
    $version_value = new Google_Service_Datastore_Value();
    $version_value->setStringValue($version);
    
    $version_arg = new Google_Service_Datastore_GqlQueryArg();
    $version_arg->setName('version');
    $version_arg->setValue($version_value);
    
    $gql_query = new Google_Service_Datastore_GqlQuery();
    $gql_query->setQueryString('SELECT * FROM version WHERE googleID = @id AND packageName = @name AND platformName = @platform AND versionName = @version');
    $gql_query->setNameArgs(array($id_arg, $name_arg, $platform_arg, $version_arg));
    
    $query = new Google_Service_Datastore_RunQueryRequest();
    $query->setGqlQuery($gql_query);
    
    $dataset = $this->datastore->datasets;
    $dataset_id = "protobuild-index";
    
    $result = $dataset->runQuery($dataset_id, $query);
    
    $batch = $result->getBatch();
    $entities = $batch->getEntityResults();
    
    if (count($entities) === 0) {
      return null;
    }
    
    $entity = head($entities);
    $entity = $entity->getEntity();
    
    self::unmapProperties($entity, $this);
    
    return $this;
  }
  
  public function loadByKey($key_id) {
    $path = new Google_Service_Datastore_KeyPathElement();
    $path->setKind(self::KIND);
    $path->setId($key_id);
    
    $key = new Google_Service_Datastore_Key();
    $key->setPath(array($path));
    
    $id_value = new Google_Service_Datastore_Value();
    $id_value->setKeyValue($key);
    
    $id_arg = new Google_Service_Datastore_GqlQueryArg();
    $id_arg->setName('key');
    $id_arg->setValue($id_value);
    
    $gql_query = new Google_Service_Datastore_GqlQuery();
    $gql_query->setQueryString('SELECT * FROM version WHERE __key__ = @key');
    $gql_query->setNameArgs(array($id_arg));
    
    $query = new Google_Service_Datastore_RunQueryRequest();
    $query->setGqlQuery($gql_query);
    
    $dataset = $this->datastore->datasets;
    $dataset_id = "protobuild-index";
    
    $result = $dataset->runQuery($dataset_id, $query);
    
    $batch = $result->getBatch();
    $entities = $batch->getEntityResults();
    
    if (count($entities) === 0) {
      return null;
    }
    
    $entity = head($entities);
    $entity = $entity->getEntity();
    
    self::unmapProperties($entity, $this);
    
    return $this;
  }
}