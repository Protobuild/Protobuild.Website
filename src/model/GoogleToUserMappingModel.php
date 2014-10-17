<?php

final class GoogleToUserMappingModel {
  
  private $loaded = false;
  private $key;
  private $googleID;
  private $user;
  
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
  
  public function getUser() {
    return $this->user;
  }
  
  public function setUser($user) {
    $this->user = $user;
    return $this;
  }
  
  public function create() {
    $path = new Google_Service_Datastore_KeyPathElement();
    $path->setKind(self::KIND);
    $path->setId(null);

    $key = new Google_Service_Datastore_Key();
    $key->setPath(array($path));
    
    $user_prop = new Google_Service_Datastore_Property();
    $user_prop->setStringValue($this->getUser());
    $user_prop->setIndexed(true);
    
    $google_id_prop = new Google_Service_Datastore_Property();
    $google_id_prop->setStringValue($this->getGoogleID());
    $google_id_prop->setIndexed(true);
    
    $entity = new Google_Service_Datastore_Entity();
    $entity->setKey($key);
    $entity->setProperties(array(
      'user' => $user_prop,
      'googleID' => $google_id_prop,
    ));

    $mutation = new Google_Service_Datastore_Mutation();
    $mutation->setInsertAutoId(array($entity));
    $req = new Google_Service_Datastore_CommitRequest();
    $req->setMode('NON_TRANSACTIONAL');
    $req->setMutation($mutation);
    
    $dataset = $this->datastore->datasets;
    $dataset_id = "protobuild-index";
    
    $dataset->commit($dataset_id, $req);
  }
  
  public function update() {
    $path = new Google_Service_Datastore_KeyPathElement();
    $path->setKind(self::KIND);
    $path->setId($this->getKey());

    $key = new Google_Service_Datastore_Key();
    $key->setPath(array($path));
    
    $user_prop = new Google_Service_Datastore_Property();
    $user_prop->setStringValue($this->getUser());
    $user_prop->setIndexed(true);
    
    $google_id_prop = new Google_Service_Datastore_Property();
    $google_id_prop->setStringValue($this->getGoogleID());
    $google_id_prop->setIndexed(true);
    
    $entity = new Google_Service_Datastore_Entity();
    $entity->setKey($key);
    $entity->setProperties(array(
      'user' => $user_prop,
      'googleID' => $google_id_prop,
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
    $props = $entity->getProperties();
    
    $this->setKey(head($entity->getKey()->getPath())->getId());
    $this->setUser(idx($props, 'user')->getStringValue());
    $this->setGoogleID(idx($props, 'googleID')->getStringValue());
    
    return $this;
  }
  
  public function loadByName($username) {
    $name_value = new Google_Service_Datastore_Value();
    $name_value->setStringValue($username);
    
    $name_arg = new Google_Service_Datastore_GqlQueryArg();
    $name_arg->setName('name');
    $name_arg->setValue($name_value);
    
    $gql_query = new Google_Service_Datastore_GqlQuery();
    $gql_query->setQueryString('SELECT * FROM user WHERE user = @name');
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
    
    $this->setKey(head($entity->getKey()->getPath())->getId());
    $this->setUser(idx($props, 'user')->getStringValue());
    $this->setGoogleID(idx($props, 'googleID')->getStringValue());
    
    return $this;
  }
  
}