<?php

final class UserModel {
  
  private $key;
  private $googleID;
  private $uniqueName;
  private $canonicalName;
  private $isOrganisation;
  
  const KIND = 'user';
  
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
  
  public function getCanonicalName() {
    return $this->canonicalName;
  }
  
  public function getUniqueName() {
    return $this->uniqueName;
  }
  
  public function setName($name) {
    $this->uniqueName = strtolower($name);
    $this->canonicalName = $name;
    return $this;
  }
  
  public function getIsOrganisation() {
    return $this->isOrganisation;
  }
  
  public function setIsOrganisation($is_organisation) {
    $this->isOrganisation = $is_organisation;
    return $this;
  }
  
  public function getTerm() {
    if ($this->getIsOrganisation()) {
      return 'organisation';
    } else {
      return 'user';
    }
  }
  
  public function getURI($path = null) {
    if ($path === null) {
      return '/'.$this->getCanonicalName();
    } else {
      return '/'.$this->getCanonicalName().'/'.$path;
    } 
  }
  
  public function canRemoveOwner($owner, $current_user){
    if (!$this->getIsOrganisation()) {
      return false;
    }
    
    return $owner->getGoogleID() !== $current_user->getGoogleID();
  }
  
  private static function unmapProperties($entity, $model) {
    $props = $entity->getProperties();
    
    $props_googleID = idx($props, 'googleID');
    $props_user = idx($props, 'canonicalName');
    $props_isOrganisation = idx($props, 'isOrganisation');
    
    $value_googleID = null;
    $value_user = null;
    $value_isOrganisation = null;
    
    if ($props_googleID !== null) {
      $value_googleID = $props_googleID->getStringValue();
    }
    
    if ($props_user !== null) {
      $value_user = $props_user->getStringValue();
    }
    
    if ($props_isOrganisation !== null) {
      $value_isOrganisation = $props_isOrganisation->getBooleanValue();
    }
    
    $model
      ->setKey(head($entity->getKey()->getPath())->getId())
      ->setGoogleID($value_googleID)
      ->setName($value_user)
      ->setIsOrganisation($value_isOrganisation);
      
    return $model;
  }
  
  public function create() {
    $path = new Google_Service_Datastore_KeyPathElement();
    $path->setKind(self::KIND);
    $path->setId(null);

    $key = new Google_Service_Datastore_Key();
    $key->setPath(array($path));
    
    $unique_name_prop = new Google_Service_Datastore_Property();
    $unique_name_prop->setStringValue($this->getUniqueName());
    $unique_name_prop->setIndexed(true);
    
    $canonical_name_prop = new Google_Service_Datastore_Property();
    $canonical_name_prop->setStringValue($this->getCanonicalName());
    
    $google_id_prop = new Google_Service_Datastore_Property();
    $google_id_prop->setStringValue($this->getGoogleID());
    $google_id_prop->setIndexed(true);
    
    $is_organisation_prop = new Google_Service_Datastore_Property();
    $is_organisation_prop->setBooleanValue($this->getIsOrganisation());
    $is_organisation_prop->setIndexed(true);
    
    $entity = new Google_Service_Datastore_Entity();
    $entity->setKey($key);
    $entity->setProperties(array(
      'uniqueName' => $unique_name_prop,
      'canonicalName' => $canonical_name_prop,
      'googleID' => $google_id_prop,
      'isOrganisation' => $is_organisation_prop,
    ));

    $mutation = new Google_Service_Datastore_Mutation();
    $mutation->setInsertAutoId(array($entity));
    $req = new Google_Service_Datastore_CommitRequest();
    $req->setMode('NON_TRANSACTIONAL');
    $req->setMutation($mutation);
    
    $dataset = $this->datastore->datasets;
    $dataset_id = "protobuild-index";
    
    $dataset->commit($dataset_id, $req);
    
    return $this;
  }
  
  public function update() {
    $path = new Google_Service_Datastore_KeyPathElement();
    $path->setKind(self::KIND);
    $path->setId($this->getKey());

    $key = new Google_Service_Datastore_Key();
    $key->setPath(array($path));
    
    $unique_name_prop = new Google_Service_Datastore_Property();
    $unique_name_prop->setStringValue($this->getUniqueName());
    $unique_name_prop->setIndexed(true);
    
    $canonical_name_prop = new Google_Service_Datastore_Property();
    $canonical_name_prop->setStringValue($this->getCanonicalName());
    
    $google_id_prop = new Google_Service_Datastore_Property();
    $google_id_prop->setStringValue($this->getGoogleID());
    $google_id_prop->setIndexed(true);
    
    $is_organisation_prop = new Google_Service_Datastore_Property();
    $is_organisation_prop->setBooleanValue($this->getIsOrganisation());
    $is_organisation_prop->setIndexed(true);
    
    $entity = new Google_Service_Datastore_Entity();
    $entity->setKey($key);
    $entity->setProperties(array(
      'uniqueName' => $unique_name_prop,
      'canonicalName' => $canonical_name_prop,
      'googleID' => $google_id_prop,
      'isOrganisation' => $is_organisation_prop,
    ));

    $mutation = new Google_Service_Datastore_Mutation();
    $mutation->setUpdate(array($entity));
    $req = new Google_Service_Datastore_CommitRequest();
    $req->setMode('NON_TRANSACTIONAL');
    $req->setMutation($mutation);
    
    $dataset = $this->datastore->datasets;
    $dataset_id = "protobuild-index";
    
    $dataset->commit($dataset_id, $req);
  }
  
  public function load($google_id) {
    $id_value = new Google_Service_Datastore_Value();
    $id_value->setStringValue($google_id);
    
    $id_arg = new Google_Service_Datastore_GqlQueryArg();
    $id_arg->setName('id');
    $id_arg->setValue($id_value);
    
    $gql_query = new Google_Service_Datastore_GqlQuery();
    $gql_query->setQueryString('SELECT * FROM user WHERE googleID = @id');
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
  
  public function loadByName($username) {
    $name_value = new Google_Service_Datastore_Value();
    $name_value->setStringValue(strtolower($username));
    
    $name_arg = new Google_Service_Datastore_GqlQueryArg();
    $name_arg->setName('name');
    $name_arg->setValue($name_value);
    
    $gql_query = new Google_Service_Datastore_GqlQuery();
    $gql_query->setQueryString('SELECT * FROM user WHERE uniqueName = @name');
    $gql_query->setNameArgs(array($name_arg));
    
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
    $props = $entity->getProperties();
    
    self::unmapProperties($entity, $this);
    
    return $this;
  }
  
  public function loadAllForIDs($google_ids) {

    $query_components = array();
    $args = array();
    $ref_id = 0;
    foreach ($google_ids as $id) {
      $id_value = new Google_Service_Datastore_Value();
      $id_value->setStringValue($id);
      
      $id_arg = new Google_Service_Datastore_GqlQueryArg();
      $id_arg->setName('id'.$ref_id);
      $id_arg->setValue($id_value);

      $args[] = $id_arg;
      $query_components[] = 'googleID = @id'.$ref_id;
    }
    
    if (count($query_components) === 0) {
      return array();
    }
    
    $query = implode(' OR ', $query_components);
    
    $gql_query = new Google_Service_Datastore_GqlQuery();
    $gql_query->setQueryString('SELECT * FROM user WHERE '.$query);
    $gql_query->setNameArgs($args);
    
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
      $props = $entity->getProperties();
      
      $results[] = 
        self::unmapProperties($entity, new UserModel());
    }
    
    return $results;
  }
  
}