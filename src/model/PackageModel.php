<?php

final class PackageModel {
  
  private $key;
  private $googleID;
  private $name;
  private $gitURL;
  private $description;
  
  const KIND = 'package';
  
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
  
  public function getName() {
    return $this->name;
  }
  
  public function setName($name) {
    $this->name = $name;
    return $this;
  }
  
  public function getGitURL() {
    return $this->gitURL;
  }
  
  public function setGitURL($git_url) {
    $this->gitURL = $git_url;
    return $this;
  }
  
  public function getDescription() {
    return $this->description;
  }
  
  public function setDescription($description) {
    $this->description = $description;
    return $this;
  }
  
  public function getFormattedDescription() {
    $html = array();
    $lines = phutil_split_lines($this->description);
    foreach ($lines as $line) {
      $html[] = phutil_tag('br', array(), null);
      $html[] = $line;
    }
    array_shift($html);
    return $html;
  }
  
  private function mapProperties() {
    $mappings = array(
      'name' => $this->getName(),
      'googleID' => $this->getGoogleID(),
      'gitURL' => $this->getGitURL(),
      'description' => $this->getDescription(),
    );
    
    $indexes = array(
      'name' => true,
      'googleID' => true
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
    
    $dataset->commit($dataset_id, $req);
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
  
  public function loadAllForUser(GoogleToUserMappingModel $user) {
    $id_value = new Google_Service_Datastore_Value();
    $id_value->setStringValue($user->getGoogleID());
    
    $id_arg = new Google_Service_Datastore_GqlQueryArg();
    $id_arg->setName('id');
    $id_arg->setValue($id_value);
    
    $gql_query = new Google_Service_Datastore_GqlQuery();
    $gql_query->setQueryString('SELECT * FROM package WHERE googleID = @id');
    $gql_query->setNameArgs(array($id_arg));
    
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
      
      $results[] = id(new PackageModel())
        ->setKey(head($entity->getKey()->getPath())->getId())
        ->setName(idx($props, 'name')->getStringValue())
        ->setGoogleID(idx($props, 'googleID')->getStringValue())
        ->setGitURL(idx($props, 'gitURL')->getStringValue())
        ->setDescription(idx($props, 'description')->getStringValue());
    }
    
    return $results;
  }
  
  public function loadByUserAndName(GoogleToUserMappingModel $user, $name) {
    $name_value = new Google_Service_Datastore_Value();
    $name_value->setStringValue($name);
    
    $name_arg = new Google_Service_Datastore_GqlQueryArg();
    $name_arg->setName('name');
    $name_arg->setValue($name_value);
    
    $id_value = new Google_Service_Datastore_Value();
    $id_value->setStringValue($user->getGoogleID());
    
    $id_arg = new Google_Service_Datastore_GqlQueryArg();
    $id_arg->setName('id');
    $id_arg->setValue($id_value);
    
    $gql_query = new Google_Service_Datastore_GqlQuery();
    $gql_query->setQueryString(
      'SELECT * FROM package WHERE googleID = @id AND name = @name');
    $gql_query->setNameArgs(array($id_arg, $name_arg));
    
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
    
    $this
      ->setKey(head($entity->getKey()->getPath())->getId())
      ->setName(idx($props, 'name')->getStringValue())
      ->setGoogleID(idx($props, 'googleID')->getStringValue())
      ->setGitURL(idx($props, 'gitURL')->getStringValue())
      ->setDescription(idx($props, 'description')->getStringValue());
    
    return $this;
  }
  
}