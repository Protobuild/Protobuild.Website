<?php

final class Panel extends Control {
  
  private $heading;
  private $noBody = false;
  private $type;
  
  public function setHeading($heading) {
    $this->heading = $heading;
    return $this;
  }
  
  public function setNoBody($no_body) {
    $this->noBody = $no_body;
    return $this;
  }
  
  public function setType($type) {
    $this->type = $type;
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
    
    $type = 'default';
    if ($this->type !== null) {
      $type = $this->type;
    }
    
    return phutil_tag(
      'div',
      array('class' => 'panel panel-'.$type),
      array(
        $heading,
        $body));
  }
  
}