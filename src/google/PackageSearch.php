<?php

final class PackageSearch {
  
  private $datastore;
  
  public function __construct() {
    $this->datastore = id(new GoogleService())->getGoogleCloudDatastore();
  }
  
  public function search($query) {
  }
  
}
