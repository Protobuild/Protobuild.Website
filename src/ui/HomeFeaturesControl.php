<?php

final class HomeFeaturesControl extends Control {
  
  public function render() {
    return hsprintf(<<<EOF
<div class="row marketing">
  <h4><span class="glyphicon glyphicon-star"></span> One executable</h4>
  <p>
    Protobuild ships as a single, 168kb executable in your repository.  Users
    don't need to install any software; just double-click Protobuild to
    generate projects.
  </p>

  <h4><span class="glyphicon glyphicon-ban-circle"></span> No duplication</h4>
  <p>
    Don't duplicate support for each platform in multiple C# projects.
    Protobuild generates C# projects for any platform from a single 
    definition, with options available to include and exclude
    resources based on the target platform.
  </p>

  <h4><span class="glyphicon glyphicon-refresh"></span> Two-way project sync</h4>
  <p>
    Reduce hand editing of project definition files.  Adding or removing 
    files in your IDE synchronises back to the project when running
    Protobuild.
  </p>

  <h4><span class="glyphicon glyphicon-th"></span> Flexible configuration</h4>
  <p>
    Protobuild offers multiple levels of customization for your projects.
    From simple option toggles to complete customization of project files,
    Protobuild allows you to output projects in the exact format you require.
  </p>

  <h4><span class="glyphicon glyphicon-folder-close"></span> Simplified libraries</h4>
  <p>
    Including a third-party library that uses Protobuild is as simple as
    <code>git submodule add</code>.  Protobuild automatically loads subfolders
    for additional projects and allows them to be referenced.
  </p>

  <h4><span class="glyphicon glyphicon-leaf"></span> Build only what you need</h4>
  <p>
    Protobuild provides a powerful dependency system, which can exclude code
    in projects when no consuming projects use that functionality.  This can
    also be used to provide alternate implementations of functionality.
  </p>

  <h4><span class="glyphicon glyphicon-ban-circle"></span> No cruft</h4>
  <p>
    Project definitions in Protobuild contain only what is needed to
    generate your projects.  Irrelevant properties and C# project
    configuration (such as developer-specific settings)
    are never tracked in source control.
  </p>
</div>
EOF
    );
  }
  
}