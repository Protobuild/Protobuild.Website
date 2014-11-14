<?php

final class IndexSearchControl extends Control {
  
  private $text;
  
  public function setText($text) {
    $this->text = $text;
    return $this;
  }
  
  public function render() {
    return hsprintf(<<<EOF
<form action="/search" method="GET">
  <div class="row marketing">
    <div class="input-group">
        <input 
          type="text" 
          id="search-packages"
          class="form-control input-lg"
          placeholder="Search package names, descriptions and owners" 
          value="%s" 
          name="q" 
          autofocus="autofocus">
        <span class="input-group-btn">
          <button class="btn btn-default btn-lg" type="submit">
            <span class="glyphicon glyphicon-search" aria-hidden="true"></span>
          </button>
        </span>
    </div>
  </div>
</form>
EOF
    , $this->text);
  }
  
}