<?php

final class FormSubmit extends Control {
  
  private $text;
  
  public function setText($text) {
    $this->text = $text;
    return $this;
  }
  
  public function render() {
    return hsprintf(<<<EOF
<button type="submit" class="btn btn-default" name="__submit__">%s</button>
EOF
    , $this->text);
  }
  
}