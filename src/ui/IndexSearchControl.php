<?php

final class IndexSearchControl extends Control {
  
  public function render() {
    return hsprintf(<<<EOF
<div class="row">
  <input class="form-control input-lg" type="text" placeholder=".input-lg">
</div>
EOF
    );
  }
  
}