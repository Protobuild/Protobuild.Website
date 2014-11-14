<?php

final class SearchController extends ProtobuildController {
  
  protected function allowPublicAccess() {
    return true;
  }
  
  public function processRequest(array $data) {
    
    $results = array();
    if (strlen(idx($_GET, 'q', '')) > 0) {
      $raw_results = id(new SearchConnector())
        ->performQuery($_GET['q']);
      
      if ($raw_results['error']) {
        $results[] = phutil_tag(
          'div',
          array(
            'class' => 'alert alert-danger',
            'role' => 'alert'),
          'Unable to retrieve search results.  Please try again later.');
      } else {
        if (count($raw_results['results']) === 0) {
          $results[] = phutil_tag(
            'p',
            array(),
            'No results for \''.$_GET['q'].'\'.');
        } else {
          $results[] = phutil_tag(
            'p',
            array(),
            'Showing '.count($raw_results['results']).
            ' results for \''.$_GET['q'].'\':');
        }
        
        foreach ($raw_results['results'] as $item) {
          $results[] = id(new Panel())
            ->setHeading(
              phutil_tag(
                'h3',
                array('class' => 'panel-title'),
                array(
                  phutil_tag('a', array('href' => '/'.$item['ownerName']), $item['ownerName']),
                  ' / ',
                  phutil_tag('a', array('href' => '/'.$item['ownerName'].'/'.$item['name']), $item['name']),
                )))
            ->appendChild(PackageModel::getStaticFormattedDescription($item['description']));
        }
      }
    }
    
    return $this->buildApplicationPage(
      array(
        id(new IndexSearchControl())
          ->setText(idx($_GET, 'q', '')),
        phutil_tag(
          'div', 
          array('class' => 'row marketing', 'id' => 'index-features'),
          $results),
      ));
  }
  
  protected function getNavigationName() {
    return 'index';
  }
  
}