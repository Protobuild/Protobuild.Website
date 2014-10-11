<?php

final class HomeBannerControl extends Control {
  
  public function render() {
    return hsprintf(<<<EOF
<div class="jumbotron">
  <h1>Cross-platform C#</h1>
  <p class="lead">
    Project generation for every platform.  Define your project content
    once and compile code for every platform, in any IDE or build system,
    on any operating system.
  </p>
  <p><a class="btn btn-lg btn-success" href="https://github.com/hach-que/Protobuild/raw/master/Protobuild.exe" role="button">Download now</a></p>
</div>
EOF
    );
  }
  
}