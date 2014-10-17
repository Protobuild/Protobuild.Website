<?php

final class GoogleToUserMappingModel {
  
  private $loaded = false;
  private $googleID;
  private $user;
  
  const KIND = 'user';
  
  public function __construct() {
    $this->datastore = id(new GoogleService())->getGoogleCloudDatastore();
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
    $google_id_prop->setIntegerValue($this->getGoogleID());
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
  
  public function load($google_id) {
    
  }
  
}