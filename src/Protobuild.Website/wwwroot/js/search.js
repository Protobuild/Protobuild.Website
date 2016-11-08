var search_packages = $("#search-packages");
var index_banner = $("#index-banner");
var index_features = $("#index-features");

var pending_query = null;
var active_request = null;

function schedule_search_request() {
  if (pending_query == null || pending_query.length == 0) {
    return;
  }
  
  active_request = "active";
  var query_copy = pending_query;
  pending_query = null;
  if ($("#search-icon").attr("class") != "fa fa-spin fa-spinner") {
    $("#search-icon").attr("class", "fa fa-spin fa-spinner");
  }
  $.ajax(
    window.SEARCH_URI + "/search/" + encodeURI(query_copy),
    {
      success: function(data) {
        index_banner.hide();
        index_features.html('');
        
        try {
          window.history.pushState(
            query_copy,
            "Protobuild Index Search: " + query_copy, 
            "/search?q=" + encodeURI(query_copy));
        } catch(err) {
          // API might not be supported.
        }
        
        if (data.error) {
          var error = $('<div></div>')
            .attr('class', 'alert alert-danger')
            .attr('role', 'alert')
            .text(
              'Unable to retrieve search results.  Please try again later.');
          index_features.append(error);
        } else {
          if (data.results.length === 0) {
            var msg = $('<p></p>')
              .text(
                'No results for \'' + query_copy + '\'.');
            index_features.append(msg);
          } else {
            var msg = $('<p></p>')
              .text(
                'Showing ' + data.results.length + ' results for \'' + query_copy + '\':');
            index_features.append(msg);
          }
          
          for (var i = 0; i < data.results.length; i++) {
            var item = data.results[i];
            var desc_target = 
              $('<div></div>')
                .attr('class', 'panel-body');
            var desc_lines = item.description.split("\n");
            for (var l = 0; l < desc_lines.length; l++) {
              if (l !== 0) {
                desc_target.append($('<br></br>'));
              }
              desc_target.append($('<span></span>').text(desc_lines[l]));
            }
            
            var entry = $('<div></div>')
              .attr('class', 'panel panel-default')
              .append(
                $('<div></div>')
                  .attr('class', 'panel-heading')
                  .append(
                    $('<h3></h3>')
                      .attr('class', 'panel-title')
                      .append(
                        $('<a></a>')
                          .attr('href', '/' + item.ownerName)
                          .text(item.ownerName))
                      .append(' / ')
                      .append(
                        $('<a></a>')
                          .attr('href', '/' + item.ownerName + '/' + item.name)
                          .text(item.name))))
              .append(desc_target);
            index_features.append(entry);
          }
        }
        
        active_request = null;
        if (pending_query != null) {
          schedule_search_request();
        } else {
          $("#search-icon").attr("class", "fa fa-search");
        }
      }
    });
}

if (search_packages.length > 0) {
  search_packages.keyup(function(ev) {
    pending_query = search_packages.val();
    
    if (window.location.pathname != '/index' && 
        window.location.pathname != '/search') {
      if (ev.keyCode == 13) {
        window.location.href = '/search?q=' + encodeURI(pending_query);
      }
      return;
    }
    
    if (active_request == null) {
      schedule_search_request();
    }
  });
}