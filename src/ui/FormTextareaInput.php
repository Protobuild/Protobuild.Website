<?php

final class FormTextareaInput extends FormControl {
  
  private $value;
  
  public function setValue($value) {
    $this->value = $value;
    return $this;
  }
  
  public function renderControl() {
    return phutil_tag(
      'textarea',
      array(
        'class' => 'form-control',
        'id' => $this->getName(),
        'name' => $this->getName(),
        'rows' => 10,
      ) + $this->getDisabledArray(),
      $this->value);
  }
  
}