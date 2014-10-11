<?php

final class IndexFeaturesControl extends Control {
  
  public function render() {
    return hsprintf(<<<EOF
<div class="row marketing">
  <h4><span class="glyphicon glyphicon-star"></span> Truely cross-platform</h4>
  <p>
    Packages that support every platform.  Rather than focusing on frameworks, 
    Protobuild focuses on platform features.  This allows libraries
    to truely work across all platforms, without sacrificing functionality.
  </p>
  
  <h4><span class="glyphicon glyphicon-refresh"></span> Source when you need it</h4>
  <p>
    Don't let package binaries prevent debugging.  Protobuild allows you to
    swap out binary packages for a source-based version, with a single command.
  </p>

  <h4><span class="glyphicon glyphicon-flash"></span> Fast submodules</h4>
  <p>
    Improve your build times by using binary packages instead of Git
    submodules.  Protobuild packages have binaries mapped to Git hashes, so
    you can get binaries for the version you use, and skip the build and Git
    history costs.
  </p>

  <h4><span class="glyphicon glyphicon-th"></span> Independent hosting</h4>
  <p>
    All Protobuild packages are referred to by their full URLs.  There's no
    preference of a central repository, which means you can easily host 
    packages on your own server or internally without additional setup.
  </p>
</div>
EOF
    );
  }
  
}