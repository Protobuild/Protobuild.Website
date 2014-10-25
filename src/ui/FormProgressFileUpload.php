<?php

final class FormProgressFileUpload extends FormControl {
  
  private $uri;
  private $redirectURI;
  
  public function setTargetURI($uri) {
    $this->uri = $uri;
    return $this;
  }
  
  public function setRedirectURI($redirect_uri) {
    $this->redirectURI = $redirect_uri;
    return $this;
  }
  
  public function renderControl() {
    $name = $this->getName();
    $uri = $this->uri;
    $redirect_uri = $this->redirectURI;
    return array(
      phutil_tag(
        'div',
        array(
          'class' => 'alert alert-danger',
          'style' => 'display: none',
          'id' => $this->getName().'-error-box',
          'name' => $this->getName().'-error-box',
        ),
        ''
      ),
      phutil_tag(
        'div',
        array(
          'class' => 'dynamic-file-upload',
          'id' => $this->getName(),
          'name' => $this->getName(),
        ),
        'Drag the package file here to upload'
      ),
      phutil_tag(
        'div',
        array(
          'class' => 'progress',
          'style' => 'display: none',
          'id' => $this->getName().'-progress-container',
          'name' => $this->getName().'-progress-container',
        ),
        phutil_tag(
          'div',
          array(
            'class' => 'progress-bar progress-bar-striped active',
            'role' => 'progressbar',
            'style' => 'width: 0%;',
            'id' => $this->getName().'-progress',
            'name' => $this->getName().'-progress',
          ),
          '0% Uploaded'
        )
      ),
      phutil_tag(
        'script',
        array(
          'type' => 'text/javascript'
        ),
        phutil_safe_html(<<<EOF
window.onload = function() { initFileUpload("$name", "$uri", "$redirect_uri"); };
EOF
      ))
    );
  }
  
}