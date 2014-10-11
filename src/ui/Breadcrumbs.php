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
    
    return phutil_tag(
      'ol',
      array('class' => 'breadcrumb'),
      $items);
  }
  
}