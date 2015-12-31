<?php

final class HomeBannerControl extends Control {
  
  public function render() {
  
    $installers = array(
      'windows' => array(
        'name' => 'Download for Windows',
        'url' => 'http://protobuild.org/get/windows',
        'cmd' => null,
      ),
      'mac' => array(
        'name' => 'Download for Mac OS X',
        'url' => null,
        'cmd' => 'curl -L http://protobuild.org/get/mac | bash',
      ),
      'linux' => array(
        'name' => 'Download for Linux',
        'url' => null,
        'cmd' => 'curl -L http://protobuild.org/get/linux | bash',
      ),
    );
    
    $agent = strtolower(idx($_SERVER, 'HTTP_USER_AGENT'));
    if (substr_count($agent, 'win') > 0) {
      $detected_os = 'windows';
    } else if (substr_count($agent, 'mac') > 0) {
      $detected_os = 'mac';
    } else if (substr_count($agent, 'linux') > 0) {
      $detected_os = 'linux';
    } else {
      $detected_os = 'windows';
    }
    
    $primary_name = idx(idx($installers, $detected_os), 'name');
    if (idx(idx($installers, $detected_os), 'url') === null) {
      $primary_uri = '#';
      $primary_click = "$('#cmdmodal').modal(); $('#command').val('" . idx(idx($installers, $detected_os), 'cmd') . "');";
    } else {
      $primary_uri = idx(idx($installers, $detected_os), 'url');
      $primary_click = '#';
    }
    
    $tags = array();
    foreach ($installers as $id => $data) {
      if ($id !== $detected_os) {
        if (idx($data, 'url') === null) {
          $uri = '#';
          $click = "$('#cmdmodal').modal(); $('#command').val('" . idx($data, 'cmd') . "');";
        } else {
          $uri = idx($data, 'url');
          $click = '#';
        }
      
        $tags[] = hsprintf(<<<EOF
<li>
  <a href="%s" onclick="%s">%s</a>
</li>   
EOF
        , $uri, $click, idx($data, 'name'));
      }
    }
  
    return hsprintf(<<<EOF
<div class="jumbotron">
  <h1>Cross-platform C#</h1>
  <p class="lead">
    Project generation for every platform.  Define your project content
    once and compile code for every platform, in any IDE or build system,
    on any operating system.
  </p>
  <p>
    <div class="btn-group btn-block" style="width: 330px;">
      <a class="col-sm-10 btn btn-lg btn-primary" style="white-space: normal;" href="%s" onclick="%s" role="button">%s</a>
      <button type="button" class="col-sm-2 btn btn-primary btn-lg dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <span class="caret"></span>
        <span class="sr-only">Toggle Dropdown</span>
      </button>
      <ul class="dropdown-menu btn-block">
        %s
        <li role="separator" class="divider"></li>
        <li><a href="https://protobuild.readthedocs.org/en/latest/getting_started_gui.html">Install GUI via the Command Line</a></li>
        <li><a href="https://protobuild.readthedocs.org/en/latest/getting_started_cmd.html">Use Command Line Tools</a></li>
      </ul>
    </div>
  </p>
</div>
<div class="modal fade" tabindex="-1" role="dialog" id="cmdmodal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
  <p class="form-inline" style="
      font-family: monospace;
      font-size: 18px;
      margin-bottom: 0px;
      margin-top: 15px;
      text-align: center;
  ">Run this from your terminal: <br><input class="form-control" id="command" value="" style="
      width: 450px;
      color: #FFF;
      background-color: #333;
      margin-top: 5px;
      margin-top: 15px;
      margin-bottom: 15px;
      text-align: center;
  "></p>
      </div>
    </div>
  </div>
</div>
EOF
    ,
    $primary_uri,
    $primary_click,
    $primary_name,
    $tags);
  }
  
}