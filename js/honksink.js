// MUSTACHE TEMPLATES

var listTemplate =  '<div class="page-header"><h1>{{heading}} <small>{{subheading}}</small></h1></div>'+
                    '<ul class="media-list">{{#honks}}<li class="media well well-small">'+
                      '<a class="pull-left" href="#">'+
                        '<img class="media-object img-circle" width="60px" height="60px" src="{{avatar}}">'+
                      '</a>'+
                      '<div class="media-body">'+
                        '<h5 class="media-heading">'+
                          '<strong></strong>'+
                          '<span class="muted">@{{username}}</span>'+
                          '<span class="pull-right">{{time_since}}</span>'+
                        '</h5>'+
                        '<p>{{honk}}</p>'+
                      '</div>'+
                      '{{#rehonk}}<i class="icon-retweet"></i><i><small> Rehonked by {{rehonkname}}</small></i>{{/rehonk}}'+
                      '{{^rehonk}}{{^me}}{{#loggedin}}<a class="btn btn-mini pull-right" href="javascript:rehonk({{honkid}})"><i class="icon-retweet"></i></a>{{/loggedin}}{{/me}}{{/rehonk}}'+
                    '</li>{{/honks}}</ul>';

var rehonkTemplate = '<div id="rehonk-modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">'+
                        '<div class="modal-header">'+
                          '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>'+
                          '<h3 id="myModalLabel">Rehonk this to the audience?</h3>'+
                        '</div>'+
                        '<div class="modal-body">'+
                          '<ul class="media-list"><li class="media">'+
                            '<a class="pull-left" href="#">'+
                              '<img class="media-object img-circle" width="60px" height="60px" src="{{avatar}}">'+
                            '</a>'+
                            '<div class="media-body">'+
                              '<h5 class="media-heading">'+
                                '<strong></strong>'+
                                '<span class="muted">@{{username}}</span>'+
                                '<span class="pull-right">{{time_since}}</span>'+
                              '</h5>'+
                              '<p>{{honk}}</p>'+
                            '</div>'+
                            '<div id="rehonk-post">'+
                              '<input type="hidden" value="{{userid}}" name="userid">'+
                              '<input type="hidden" value="{{honk}}" name="honk">'+
                              '<input type="hidden" name="rehonk">'+
                            '</div>'+
                          '</li></ul>'+
                        '</div>'+
                        '<div class="modal-footer">'+
                          '<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>'+
                          '<a type="submit" id="user_update" class="btn btn-primary" href="javascript:postRehonk()">Rehonk</a>'+
                        '</div>'+
                    '</div>';

var msgTemplate = '<div class="alert {{type}}">'+
                    '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                    '<strong>{{title}}</strong> {{msg}}'+
                  '</div>';

// CONFIG
var opts = {
  lines: 7, // The number of lines to draw
  length: 7, // The length of each line
  width: 19, // The line thickness
  radius: 19, // The radius of the inner circle
  corners: 0.7, // Corner roundness (0..1)
  rotate: 0, // The rotation offset
  direction: 1, // 1: clockwise, -1: counterclockwise
  color: '#000', // #rgb or #rrggbb
  speed: 1.8, // Rounds per second
  trail: 70, // Afterglow percentage
  shadow: false, // Whether to render a shadow
  hwaccel: false, // Whether to use hardware acceleration
  className: 'spinner', // The CSS class to assign to the spinner
  zIndex: 2e9, // The z-index (defaults to 2000000000)
  top: 'auto', // Top position relative to parent in px
  left: 'auto' // Left position relative to parent in px
};

// GET & POST METHODS
function getAllHonks() {
  var target = document.getElementById('container');
  var spinner = new Spinner(opts).spin(target);
  $.getJSON('http://labs.doversten.se/honksink/request.php?getAllHonks', function(data) {
    console.log(data);
    data.heading = "Honks";
    data.subheading = "Spread the honks!";
    var html = Mustache.to_html(listTemplate, data);
    $('#content-area').html(html);
    spinner.stop();
  });
}

function getMyHonks() {
  var target = document.getElementById('container');
  var spinner = new Spinner(opts).spin(target);
  $.getJSON('http://labs.doversten.se/honksink/request.php?getMyHonks', function(data) {
    console.log(data);
    data.heading = "@"+data.username;
    data.subheading = "Your smart honks!";
    var html = Mustache.to_html(listTemplate, data);
    $('#content-area').html(html);
    spinner.stop();
  });
}

function search() {
  var target = document.getElementById('container');
  var spinner = new Spinner(opts).spin(target);

  var text = $('input[id=searchbox]').val();

  $.getJSON('http://labs.doversten.se/honksink/request.php?search='+text, function(data) {
    console.log(data);
    data.heading = "You searched for:";
    data.subheading = text;
    var html = Mustache.to_html(listTemplate, data);
    $('#content-area').html(html);
    spinner.stop();
  });
}

function login() {
  var data = $('#login-post :input').serialize();
  
  $.ajax({
    type: "POST", 
    url: "request.php?login", 
    data: data,
    success: function(data){
      // TODO: Proper error handling
      console.log(data);
      if(data == 'success') {
        location.reload();
      } else {
        var msgData = {};
        msgData.title = 'Error: ';
        msgData.type = 'alert-error';
        msgData.msg = data;
        var html = Mustache.to_html(msgTemplate, msgData);
        $('#msg-area').html(html);
      }
    }
  });
}

function signup() {
  var data = $('#signup-post :input').serialize();
  
  $.ajax({
    type: "POST", 
    url: "request.php?signup",
    data: data,
    success: function(data){
      // TODO: Proper error handling
      console.log(data);
      if(data == 'success') {
        location.reload();
      } else {
        var msgData = {};
        msgData.title = 'Error: ';
        msgData.type = 'alert-error';
        msgData.msg = data;
        var html = Mustache.to_html(msgTemplate, msgData);
        $('#msg-area').html(html);
      }
    }
  });
}

function postHonk() {
  var data = $('#honk-post :input').serialize();
  
  $.ajax({
    type: "POST", 
    url: "request.php?insert", 
    data: data,
    success: function(data){
      // TODO: Proper error handling
      console.log(data);
      $('#honkbox').val('');
      getAllHonks();
    }
  });
}

function postRehonk() {
  var data = $('#rehonk-post :input').serialize();

  $.ajax({
    type: "POST", 
    url: "request.php?insert", 
    data: data,
    success: function(data){
      // TODO: Proper error handling
      console.log(data);
      $('#rehonk-modal').modal('hide');
      getAllHonks();
    }
  });
}

// HELP METHODS

function rehonk(id) {
  $.getJSON('http://labs.doversten.se/honksink/request.php?getHonk='+id, function(data) {
    var html = Mustache.to_html(rehonkTemplate, data);
    $('#rehonk-area').html(html);
    $('#rehonk-modal').modal('show');
  });
}

// ON LOAD / DOCUMENT READY

$(document).ready(function(){
  
  getAllHonks();

  $("#honk_delete").click(function() {
    console.log(this.attr("honkid"));
    //$('.media-list').empty();
    //getMyHonks();
  });

  $("form#updateform").submit(function() {

      var url = "request.php?update";

      var formData = new FormData($(this)[0]);

      $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        async: false,
        success: function (data) {
          if(data.status == 'success') {
            $('#myModal').modal('hide');
            $('.media-list').empty();
            getAllHonks();
          } else if (data.status == 'error') {
            console.log(data);
          } else {
            console.log(data);
          }
        },
        cache: false,
        contentType: false,
        processData: false
      });

      return false; // avoid to execute the actual submit of the form.
  });

});