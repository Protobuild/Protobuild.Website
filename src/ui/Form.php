<?php

final class Form extends Control {
  
  public function render() {
    return hsprintf(<<<EOF
<form role="form" method="POST" enctype="multipart/form-data">%s</form>
EOF
    , $this->renderChildren());
  }
  
}