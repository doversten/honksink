<?php
session_start();

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>HonkSink</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="css/bootstrap.css" rel="stylesheet">
    <style>
      body {
        padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
      }
      .img-circle {
        height: 60px;
      }
    </style>
    <link href="css/bootstrap-responsive.css" rel="stylesheet">
    <link href="css/bootstrap-fileupload.min.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="../assets/js/html5shiv.js"></script>
    <![endif]-->

    <!-- Fav and touch icons -->
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="ico/apple-touch-icon-57-precomposed.png">
    <link rel="shortcut icon" href="ico/favicon.png">

  </head>

  <body>
    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="brand" href="javascript:getAllHonks()">HonkSink</a>
          <?php
            $hash = md5('somethingSecret' . $_SERVER['HTTP_USER_AGENT']. session_id());

            if(isset($_SESSION['login']) && $_SESSION['login'] == $hash) {
            ?>

              <div style="margin-left:10px;" class="btn-group pull-right">
                <a class="btn btn-inverse" href="javascript:getMyHonks()"><i class="icon-user icon-white"></i>@<? echo $_SESSION["username"]; ?></a>
                <a class="btn btn-inverse dropdown-toggle" data-toggle="dropdown" href="#"><span class="caret"></span></a>
                <ul class="dropdown-menu">
                  <li><a href="#myModal" role="button" data-toggle="modal"><i class="icon-pencil"></i> Edit Profile</a></li>
                  <li class="divider"></li>
                  <li><a href="request.php?logout"><i class="icon-off"></i> Logout</a></li>
                </ul>
              </div>
              <!--<div class="navbar-form pull-right form-search">
                <div class="input-append">
                  <input id="searchbox" type="text" class="span2" name="search">
                  <a class="btn btn-inverse" href="javascript:search()"><i class="icon-search icon-white"></i></a>
                </div>
              </div>-->
            <?php } ?>
        </div>
      </div>
    </div>

    <div id="container" class="container">

      <div id="rehonk-area"></div>

      <!-- Modal -->
      <div id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <form class="form-horizontal" id="updateform" enctype="multipart/form-data" method="post">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="myModalLabel">Edit Profile</h3>
          </div>
          <div class="modal-body">
            <div class="row-fluid">
              <div class="span6">
                <div class="control-group">
                  <input type="text" name="user" id="user" placeholder="Email" value="<?php echo $_SESSION['email']; ?>">
                </div>
                <div class="control-group">
                  <input type="text" name="user" id="user" placeholder="Username" value="<?php echo $_SESSION['username']; ?>">
                </div>
                <div class="fileupload fileupload-new" data-provides="fileupload">
                  <div class="fileupload-new thumbnail" style="width: 60px; height: 60px;"><img src="<?php echo $_SESSION['avatar']; ?>" /></div>
                  <div class="fileupload-preview fileupload-exists thumbnail" style="width: 60px; height: 60px;"></div>
                  <span class="btn btn-file"><span class="fileupload-new">Select Profile Pic</span><span class="fileupload-exists">Change</span><input type="file" name="profilepic" /></span>
                  <a href="#" class="btn fileupload-exists" data-dismiss="fileupload">Remove</a>
                </div>
              </div>
              <div class="span6">
                <div class="control-group">
                  <input type="text" name="user" id="user" placeholder="Old Password">
                </div>
                <div class="control-group">
                  <input type="text" name="user" id="user" placeholder="New Password">
                </div>
                <div class="control-group">
                  <input type="password" name="password" id="password" placeholder="New Password Again">
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
            <button type="submit" id="user_update" class="btn btn-primary">Save changes</button>
          </form>
        </div>
      </div>

      <div id="msg-area"></div>

      <div class="row-fluid">
        <div id="content-area" class="span7">
        </div>

        <?php
        $hash = md5('somethingSecret' . $_SERVER['HTTP_USER_AGENT']. session_id());

        if(isset($_SESSION['login']) && $_SESSION['login'] == $hash) {
        ?>
        <div class="span4 offset1">
            <div class="well well-small">
              <div id="honk-post" class="control-group">
                <textarea style="width: 335px; min-width: 335px; max-width: 335px; height: 80px; min-height: 80px; max-height: 80px;" name="honk" id="honkbox" placeholder="Enter your honk here..."></textarea>
              </div>
              <div style="margin-bottom: 55px;">
                <a class="btn btn-info pull-right" href="javascript:postHonk()">Honk</a>
              </div>
            </div>
        </div>
        <?php } else { ?>
        <div class="span3 offset2">
          <div class="row">
            <div id="login-post" class="well">
            <!--<form class="form-horizontal" action="request.php?login" method="post">-->
              <div class="control-group">
                  <input type="text" name="user" id="user" placeholder="Email / Username">
              </div>
              <div class="control-group">
                  <input type="password" name="password" id="password" placeholder="Password">
              </div>
              <div class="control-group">
                  <input type="hidden" name="submit">
                  <!--<button type="submit" name="submit" value="submit" class="btn btn-info">Sign in</button>-->
                  <a href="javascript:login()" class="btn btn-info">Sign in</a>
              </div>
            <!--</form>-->
            </div>
          </div>
          <div class="row">
            <div class="well">
            <div id="signup-post" class="form-horizontal" action="request.php?signup" id="signupform" method="post">
              <div class="control-group">
                <input type="text" name="email" id="email" placeholder="Email">
              </div>
              <div class="control-group">
                <input type="text" name="username" id="username" placeholder="Username">
              </div>
              <div class="control-group">
                <input type="password" name="password" id="password" placeholder="Password">
              </div>
              <div class="control-group">
                  <!--<button type="submit" class="btn btn-warning button">Sign up for HonkSink</button>-->
                  <a href="javascript:signup()" class="btn btn-warning button">Sign up for HonkSink</a>
              </div>
            </div>
            </div>
          </div>
        </div>
        <?php } ?>

      </div>

    </div> <!-- /container -->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script type="text/javascript" src="http://code.jquery.com/jquery.min.js"></script>
    <script type="text/javascript" src="js/bootstrap.min.js"></script>
    <script type="text/javascript" src="js/bootstrap-fileupload.min.js"></script>
    <script type="text/javascript" src="js/mustache.js"></script>
    <script type="text/javascript" src="js/spin.min.js"></script>
    <script type="text/javascript" src="js/honksink.js"></script>
  </body>
</html>
