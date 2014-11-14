<?php

final class Breadcrumbs extends Control {
  
  private $breadcrumbs = array();
  
  public function addBreadcrumb($text, $uri = null) {
    $this->breadcrumbs[] = array('text' => $text, 'uri' => $uri);
  }
  
  public function render() {
    $items = array();
    foreach ($this->breadcrumbs as $data) {
      if ($data['uri'] !== null) {
        $items[] = phutil_tag(
          'li',
          array(),
            phutil_tag(
            'a',
            array('href' => $data['uri']),
            $data['text']));
      } else {
        $items[] = phutil_tag(
          'li',
          array('class' => 'active'),
          $data['text']);
      }
    }
    
    $search = hsprintf(<<<EOF
<form action="/search" method="GET" id="breadcrumb-search-form">
  <div id="breadcrumb-search" class="input-group">
    <input type="text" id="search-packages" class="form-control" placeholder="Search" name="q">
    <span class="input-group-btn">
      <button class="btn btn-default" type="submit">
        <span class="glyphicon glyphicon-search" aria-hidden="true"></span>
      </button>
    </span>
  </div>
</form>
EOF
    );
    
    return array(
      $search,
        phutil_tag(
        'ol',
        array('class' => 'breadcrumb'),
        $items));
  }
  
}