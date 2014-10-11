<?php

final class IndexBannerControl extends Control {
  
  public function render() {
    return hsprintf(<<<EOF
<div class="jumbotron">
  <h1>Protobuild packages</h1>
  <p class="lead">
    Packages without the pain.  Cross-platform packages that
    let you switch out binaries for source code.
  </p>
  <p><button type="button" class="btn btn-lg btn-primary" disabled="disabled" href="#">Coming soon</button></p>
</div>
EOF
    );
  }
  
}