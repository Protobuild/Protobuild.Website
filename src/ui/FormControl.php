<?php

abstract class FormControl extends Control {
  
  private $name;
  private $label;
  private $error;
  private $caption;
  
  public function setName($name) {
    $this->name = $name;
    return $this;
  }
  
  public function getName() {
    return $this->name;
  }
  
  public function setLabel($label) {
    $this->label = $label;
    return $this;
  }
  
  public function getLabel() {
    return $this->label;
  }
  
  public function setError($error) {
    $this->error = $error;
    return $this;
  }
  
  public function getError() {
    return $this->error;
  }
  
  public function setCaption($caption) {
    $this->caption = $caption;
    return $this;
  }
  
  public function getCaption() {
    return $this->caption;
  }
  
  protected abstract function renderControl();
  
  public function render() {
    if ($this->name === null) {
      throw new Exception('Name not set for form control.');
    }
    
    if ($this->label === null) {
      throw new Exception('Label not set for form control.');
    }
    
    $error_class = null;
    $error_message = null;
    if ($this->error !== null) {
      $error_class = 'has-error';
      $error_message = ' ('.$this->error.')';
    }
    
    $caption = null;
    if ($this->caption !== null) {
      $this->caption = phutil_tag(
        'p',
        array('class' => 'help-block'),
        $this->caption);
    }
    
    return hsprintf(<<<EOF
<div class="form-group %s">
  <label class="control-label" for="%s">%s%s</label>
  %s
  %s
</div>
EOF
    ,
    $error_class,
    $this->name,
    $this->label,
    $error_message,
    $this->renderControl(),
    $this->caption);
  }
  
}