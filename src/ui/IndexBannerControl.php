<?php

final class IndexBannerControl extends Control {
  
  public function render() {
    return hsprintf(<<<EOF
<div class="jumbotron" id="index-banner">
  <h1>Protobuild packages</h1>
  <p class="lead">
    Packages without the pain.  Cross-platform packages that
    let you switch between binaries and source code seamlessly.
  </p>
</div>
EOF
    );
  }
  
}