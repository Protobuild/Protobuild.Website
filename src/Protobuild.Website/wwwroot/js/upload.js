function initFileUpload(element_id, uri, returnURI) {
  
  var elem = document.getElementById(element_id);
  var errorBox = document.getElementById(element_id + '-error-box');
  var progressContainer = document.getElementById(element_id + '-progress-container');
  var progressBar = document.getElementById(element_id + '-progress');
  
  elem.ondragover = function() { 
    elem.className = 'dynamic-file-upload-hover';
    return false;
  };
  
  elem.ondragend = function() {
    elem.className = 'dynamic-file-upload';
    return false; 
  };
  
  elem.ondrop = function(e) {
    elem.className = 'dynamic-file-upload';
    e.preventDefault();
    
    $(progressBar).attr('aria-valuenow', '0');
    progressContainer.style.display = '';
    progressBar.style.width = 'auto';
    progressBar.style.marginLeft = '6px';
    progressBar.innerHTML = 'Please Wait';
    errorBox.style.display = 'none';
    elem.style.display = 'none';
    
    var file = e.dataTransfer.files[0];
    
    var makingFinalizeRequest = false;
    
    var finalize = function() {
      if (!makingFinalizeRequest) {
        makingFinalizeRequest = true;
        progressBar.className = 'progress-bar progress-bar-success progress-bar-striped active';
        progressBar.innerHTML = 'Finishing Upload';
        
        // Chrome falsely "fails" the request because the PUT doesn't return
        // with a Cross-Origin header, even though it successfully makes the
        // PUT request and the data ends up in Cloud Storage.  Basically the
        // only outcome of this is that we can't read the response of the PUT,
        // so instead we treat it as "if all of the bytes were uploaded, then
        // we were successful".
        var finalize = new XMLHttpRequest();
        finalize.onreadystatechange = function(evt) {
          if (finalize.readyState == 4) {
            progressBar.className = 'progress-bar progress-bar-success';
            progressBar.innerHTML = 'Redirecting';
            window.location.replace(returnURI);
          }
        }
        finalize.open('POST', window.location.href, true);
        finalize.setRequestHeader("Content-type","application/x-www-form-urlencoded");
        finalize.send('uploaded=true&archiveType=tar/gzip');
      }
    }
    
    var request = new XMLHttpRequest();
    request.upload.onprogress = function(evt) {
      if (evt.lengthComputable) {
        var percent = Math.round((evt.loaded / evt.total) * 100);
        if (Math.round(percent) < 3) {
          $(progressBar).attr('aria-valuenow', Math.round(percent) + '');
          progressBar.style.width = 'inherit';
        } else if (Math.round(percent) < 5) {
          $(progressBar).attr('aria-valuenow', '2');
          progressBar.style.width = 'inherit';
        } else {
          $(progressBar).attr('aria-valuenow', Math.round(percent) + '');
          progressBar.style.width = percent + '%';
        }
        progressBar.style.marginLeft = '';
        progressBar.innerHTML = percent + '%';
        if (evt.loaded === evt.total) {
          finalize();
        }
      } else {
        progressBar.innerHTML = 'Starting Upload';
      }
    }
    request.open('PUT', uri, true);
    
    var hasError = false;
    var showError = function(err) {
      if (hasError) {
        return;
      }
      hasError = true;
      
      elem.style.display = '';
      errorBox.style.display = '';
      progressContainer.style.display = 'none';
      
      $(progressBar).attr('aria-valuenow', '0');
      progressBar.style.width = 'auto';
      progressBar.style.marginLeft = '6px';
      progressBar.innerHTML = 'Please Wait';
      
      $(errorBox).text(err);
    };
    
    var reader = new FileReader();
    reader.onload = function(event) {
      console.log(event.target);
      
      progressBar.innerHTML = 'Validating File';
      
      var mustSee = {
        'Build/': false,
        'Build/Module.xml': false,
        'Build/Projects/': false,
        'Protobuild.exe': false,
      }
      
      TarGZ.load(
        event.target.result,
        function(header) {
          if (hasError) {
            return;
          }
          
          var valid = true;
          $.each(mustSee, function(index, value) {
            if (index === 'Protobuild.exe') {
              return;
            }
            if (value !== true) {
              valid = false;
            }
          });
          
          if (mustSee['Protobuild.exe'] === true) {
            showError(
              'Do not include Protobuild.exe in your package file.');
            return;
          }
          
          if (!valid) {
            showError(
              'Missing the Build folder, Build/Projects folder or ' + 
              'Build/ModuleInfo.xml file.  Make sure the Build folder ' + 
              'is at the root of the package, and is not contained ' +
              'within a subdirectory.');
          } else {
            request.send(new DataView(event.target.result));
          }
        },
        function(unpackedFile, header) {
          if (hasError) {
            return;
          }
          
          if (unpackedFile.filename.indexOf("PaxHeaders") > 0) {
            showError(
              'PaxHeaders detected.  Make sure you create the TAR file in ' +
              'GNU format, and not POSIX format.  If you are creating the ' +
              'TAR file from Linux, you can use \'tar --format=gnu -cf output.tar *\' ' +
              'to get the expected format.');
          }
          
          if (mustSee[unpackedFile.filename] !== undefined) {
            mustSee[unpackedFile.filename] = true;
          }
        },
        function(error) {
          if (hasError) {
            return;
          }
          
          showError(error);
        }
      );
    };
    console.log(file);
    reader.readAsArrayBuffer(file);
    
    return false;
  };
  
}