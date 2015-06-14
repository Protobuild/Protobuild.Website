<?php

final class IndexFeaturesControl extends Control {
  
  public function render() {
    return hsprintf(<<<EOF
<div class="row marketing" id="index-features">
  <h4><span class="glyphicon glyphicon-star"></span> Truely cross-platform</h4>
  <p>
    Packages that support every platform.  Rather than focusing on frameworks, 
    Protobuild focuses on platform features.  This allows libraries
    to truely work across all platforms, without sacrificing functionality and
    without expensive runtime checks.
    <a href="https://protobuild.readthedocs.org/en/latest/package_management_protobuild.html">
    Find out more</a>.
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

  <h4><span class="glyphicon glyphicon-hdd"></span> Source code caching</h4>
  <p>
    Source versions of packages have their Git history cached locally.  When you
    add the same library to another project, it will fetch and then clone from
    your local cache, drastically reducing the time required to add source-based
    libraries.
  </p>

  <h4><span class="glyphicon glyphicon-th"></span> Independent hosting</h4>
  <p>
    All Protobuild packages are referred to by their full URLs.  There's no
    preference of a central repository, which means you can easily host 
    packages on your own server or internally without additional setup.
  </p>

  <h4><span class="glyphicon glyphicon-fire"></span> NuGet support</h4>
  <p>
    You can now include packages from a NuGet repository directly (cross-platform
    support subject to the NuGet package you are including). 
    <a href="https://protobuild.readthedocs.org/en/latest/package_management_nuget.html">
    Find out more</a>.
  </p>
</div>
EOF
    );
  }
  
}