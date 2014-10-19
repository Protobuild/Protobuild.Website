<?php

final class FormTextInput extends FormControl {
  
  private $value;
  private $placeholder;
  
  public function setValue($value) {
    $this->value = $value;
    return $this;
  }
  
  public function setPlaceholder($placeholder) {
    $this->placeholder = $placeholder;
    return $this;
  }
  
  public function renderControl() {
    return phutil_tag(
      'input',
      array(
        'type' => 'text',
        'class' => 'form-control',
        'id' => $this->getName(),
        'name' => $this->getName(),
        'placeholder' => $this->placeholder,
        'value' => $this->value,
      ),
      null);
  }
  
}