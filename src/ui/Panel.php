<?php

final class Panel extends Control {
  
  private $heading;
  private $noBody = false;
  
  public function setHeading($heading) {
    $this->heading = $heading;
    return $this;
  }
  
  public function setNoBody($no_body) {
    $this->noBody = $no_body;
    return $this;
  }
  
  public function render() {
    
    $heading = null;
    if ($this->heading !== null) {
      $heading = phutil_tag(
        'div',
        array('class' => 'panel-heading'),
        $this->heading);
    }
    
    if ($this->noBody) {
      $body = $this->renderChildren();
    } else {
      $body = phutil_tag(
        'div',
        array('class' => 'panel-body'),
        $this->renderChildren());
    }
    
    return phutil_tag(
      'div',
      array('class' => 'panel panel-default'),
      array(
        $heading,
        $body));
  }
  
}