<?php

final class ProtobuildErrorController extends ProtobuildController {
  
  private $exception;
  private $code;
  
  public function setException($exception) {
    $this->exception = $exception;
  }
  
  public function setCode($code) {
    $this->code = $code;
  }
  
  protected function allowPublicAccess() {
    return true;
  }
  
  public function processRequest(array $data) {
    $title = '404';
    if ($this->code === 500) {
      $title = '&times;';
    }
    
    // Requires PHP 5.4.0 or higher.
    http_response_code($this->code);
    
    return $this->buildApplicationPage(hsprintf(<<<EOF
<div class="fourohfour">
$title
</div>
<div class="alert alert-danger error-message" role="alert">
%s
</div>
EOF
    , $this->exception->getProtobuildMessage()));
  }
  
  public function processApi(array $data) {
    return array(
      'has_error' => true,
      'error' => $this->exception->getProtobuildMessage(),
      'result' => null,
    );
  }
  
}