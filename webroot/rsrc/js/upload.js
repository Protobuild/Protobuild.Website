function initFileUpload(element_id, uri, returnURI) {
  
  var elem = document.getElementById(element_id);
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
    
    progressContainer.style.display = '';
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
        finalize.send('uploaded=true');
      }
    }
    
    var request = new XMLHttpRequest();
    request.upload.onprogress = function(evt) {
      if (evt.lengthComputable) {
        var percent = Math.round((evt.loaded / evt.total) * 100);
        progressBar.style.width = percent + '%';
        progressBar.innerHTML = percent + '%';
        if (evt.loaded === evt.total) {
          finalize();
        }
      } else {
        progressBar.innerHTML = 'Starting Upload';
      }
    }
    request.open('PUT', uri, true);
    
    var reader = new FileReader();
    reader.onload = function(event) { 
      console.log(event.target);
      request.send(new DataView(event.target.result));
    };
    console.log(file);
    reader.readAsArrayBuffer(file);
    
    return false;
  };
  
}