<?php

final class UserModel {
  
  private $key;
  private $googleID;
  private $uniqueName;
  private $canonicalName;
  private $isOrganisation;
  private $apiKey;
  
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
  
  public function getApiKey() {
    return $this->apiKey;
  }
  
  public function setApiKey(PhutilOpaqueEnvelope $key = null) {
    $this->apiKey = $key;
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
    if ($current_user === null) {
      return false;
    }
    
    if (!$this->getIsOrganisation()) {
      return false;
    }
    
    return $owner->getGoogleID() !== $current_user->getGoogleID();
  }
  
  public function getJSONArray() {
    return array(
      'id' => $this->getGoogleID(),
      'canonicalName' => $this->getCanonicalName(),
      'uniqueName' => $this->getUniqueName(),
      'isOrganisation' => $this->getIsOrganisation(),
      'term' => $this->getTerm(),
      'url' => ProtobuildEnv::get('domain').$this->getURI(),
      'apiUrl' => make_api_url(ProtobuildEnv::get('domain').$this->getURI()),
    );
  }
  
  protected function getAndOpenApiKey() {
    $api_key = $this->getApiKey();
    
    if ($api_key === null) {
      return null;
    }
    
    return $api_key->openEnvelope();
  }
  
  public function getOrGenerateAndSaveApiKey() {
    $api_key = $this->getApiKey();
    
    while ($api_key === null) {
      $proposed_key = new PhutilOpaqueEnvelope(
        Filesystem::readRandomCharacters(32));
      
      $existing_user = id(new UserModel())->loadByApiKey($proposed_key);
      if ($existing_user === null) {
        $api_key = $proposed_key;
      }
    }
    
    if ($this->getApiKey() === null) {
      $this->setApiKey($api_key)->update();
    }
    
    return $api_key;
  }
  
  private static function unmapProperties($entity, $model) {
    $props = $entity->getProperties();
    
    $props_googleID = idx($props, 'googleID');
    $props_user = idx($props, 'canonicalName');
    $props_isOrganisation = idx($props, 'isOrganisation');
    $props_apiKey = idx($props, 'apiKey');
    
    $value_googleID = null;
    $value_user = null;
    $value_isOrganisation = null;
    $value_apiKey = null;
    
    if ($props_googleID !== null) {
      $value_googleID = $props_googleID->getStringValue();
    }
    
    if ($props_user !== null) {
      $value_user = $props_user->getStringValue();
    }
    
    if ($props_isOrganisation !== null) {
      $value_isOrganisation = $props_isOrganisation->getBooleanValue();
    }
    
    if ($props_apiKey !== null) {
      $value_apiKey = new PhutilOpaqueEnvelope($props_apiKey->getStringValue());
    }
    
    $model
      ->setKey(head($entity->getKey()->getPath())->getId())
      ->setGoogleID($value_googleID)
      ->setName($value_user)
      ->setIsOrganisation($value_isOrganisation)
      ->setApiKey($value_apiKey);
      
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
    
    $api_key_prop = new Google_Service_Datastore_Property();
    $api_key_prop->setStringValue($this->getAndOpenApiKey());
    $api_key_prop->setIndexed(true);
    
    $entity = new Google_Service_Datastore_Entity();
    $entity->setKey($key);
    $entity->setProperties(array(
      'uniqueName' => $unique_name_prop,
      'canonicalName' => $canonical_name_prop,
      'googleID' => $google_id_prop,
      'isOrganisation' => $is_organisation_prop,
      'apiKey' => $api_key_prop,
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
    
    $api_key_prop = new Google_Service_Datastore_Property();
    $api_key_prop->setStringValue($this->getAndOpenApiKey());
    $api_key_prop->setIndexed(true);
    
    $entity = new Google_Service_Datastore_Entity();
    $entity->setKey($key);
    $entity->setProperties(array(
      'uniqueName' => $unique_name_prop,
      'canonicalName' => $canonical_name_prop,
      'googleID' => $google_id_prop,
      'isOrganisation' => $is_organisation_prop,
      'apiKey' => $api_key_prop,
    ));

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
    
    self::unmapProperties($entity, $this);
    
    return $this;
  }
  
  public function loadByApiKey(PhutilOpaqueEnvelope $api_key) {
    $api_key_value = new Google_Service_Datastore_Value();
    $api_key_value->setStringValue($api_key->openEnvelope());
    
    $api_key_arg = new Google_Service_Datastore_GqlQueryArg();
    $api_key_arg->setName('key');
    $api_key_arg->setValue($api_key_value);
    
    $gql_query = new Google_Service_Datastore_GqlQuery();
    $gql_query->setQueryString('SELECT * FROM user WHERE apiKey = @key');
    $gql_query->setNameArgs(array($api_key_arg));
    
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