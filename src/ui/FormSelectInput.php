<?php

final class FormSelectInput extends FormControl {
  
  private $value;
  private $options;
  
  public function setValue($value) {
    $this->value = $value;
    return $this;
  }
  
  public function setOptions($map) {
    $this->options = $map;
    return $this;
  }
  
  public function renderControl() {
    $options = array();
    
    foreach ($this->options as $key => $value) {
      $selected = array();
      if ($this->value === $key) {
        $selected = array('selected' => 'selected');
      }
      
      $options[] = phutil_tag(
        'option',
        array(
          'value' => $key,
        ) + $selected,
        $value
      );
    }
    
    return phutil_tag(
      'select',
      array(
        'class' => 'form-control',
        'id' => $this->getName(),
        'name' => $this->getName(),
      ) + $this->getDisabledArray(),
      $options
    );
  }
  
}