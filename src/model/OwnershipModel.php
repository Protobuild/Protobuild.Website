<?php

final class OwnershipModel {
  
  private $key;
  private $ownerGoogleID;
  private $organisationGoogleID;
  
  const KIND = 'ownership';
  
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
  
  public function getOwnerGoogleID() {
    return $this->ownerGoogleID;
  }
  
  public function setOwnerGoogleID($googleID) {
    $this->ownerGoogleID = $googleID;
    return $this;
  }
  
  public function getOrganisationGoogleID() {
    return $this->organisationGoogleID;
  }
  
  public function setOrganisationGoogleID($googleID) {
    $this->organisationGoogleID = $googleID;
    return $this;
  }
  
  private function mapProperties() {
    $mappings = array(
      'ownerGoogleID' => $this->getOwnerGoogleID(),
      'organisationGoogleID' => $this->getOrganisationGoogleID(),
    );
    
    $indexes = array(
      'ownerGoogleID' => true,
      'organisationGoogleID' => true,
    );
    
    $results = array();
    
    foreach ($mappings as $name => $value) {
      $prop = new Google_Service_Datastore_Property();
      $prop->setStringValue($value);
      $prop->setIndexed(array_key_exists($name, $indexes));
      
      $results[$name] = $prop;
    }
    
    return $results;
  }
  
  private static function unmapProperties($entity, $model) {
    $props = $entity->getProperties();
    
    $props_ownerGoogleID = idx($props, 'ownerGoogleID');
    $props_organisationGoogleID = idx($props, 'organisationGoogleID');
    
    $value_ownerGoogleID = null;
    $value_organisationGoogleID = null;
    
    if ($props_ownerGoogleID !== null) {
      $value_ownerGoogleID = $props_ownerGoogleID->getStringValue();
    }
    
    if ($props_organisationGoogleID !== null) {
      $value_organisationGoogleID = $props_organisationGoogleID->getStringValue();
    }
    
    $model
      ->setKey(head($entity->getKey()->getPath())->getId())
      ->setOwnerGoogleID($value_ownerGoogleID)
      ->setOrganisationGoogleID($value_organisationGoogleID);
      
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
    return $this;
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
  
  public function loadOwnersForOrganisationGoogleID($google_id) {
    $name_value = new Google_Service_Datastore_Value();
    $name_value->setStringValue($google_id);
    
    $name_arg = new Google_Service_Datastore_GqlQueryArg();
    $name_arg->setName('name');
    $name_arg->setValue($name_value);
    
    $gql_query = new Google_Service_Datastore_GqlQuery();
    $gql_query->setQueryString('SELECT * FROM ownership WHERE organisationGoogleID = @name');
    $gql_query->setNameArgs(array($name_arg));
    
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
        id(new OwnershipModel()));
    }
    
    return $results;
  }
}