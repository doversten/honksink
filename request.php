<?php
    session_start();

    function humanTiming ($time) {

        $time = time() - $time; // to get the time since that moment

        if($time < 1) {
          return "0 seconds";
        }

        $tokens = array (
            31536000 => 'year',
            2592000 => 'month',
            604800 => 'week',
            86400 => 'day',
            3600 => 'hour',
            60 => 'minute',
            1 => 'second'
        );

        foreach ($tokens as $unit => $text) {
            if ($time < $unit) continue;
            $numberOfUnits = floor($time / $unit);
            return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
        }
    }

    if(isset($_GET['logout'])) {
        unset($_SESSION['login']);
        unset($_SESSION['userid']);
        unset($_SESSION["username"]);
        unset($_SESSION["email"]);
        unset($_SESSION["avatar"]);
        header("Location: index.php");
    }

    if(isset($_GET['login']))
    {
        $hash = md5('somethingSecret' . $_SERVER['HTTP_USER_AGENT']. session_id());

        if (isset($_SESSION['login']) && $_SESSION['login'] == $hash) {
            echo 'success';
            exit();

        } else if (isset($_POST['submit'])) {
            $con = mysqli_connect("localhost","username","password","database");
            
            // Check connection
            if (mysqli_connect_errno()) {
              die("Failed to connect to MySQL: " . mysqli_connect_error());
            }
            
            $sql='SELECT * FROM users WHERE username = "' . $_POST['user'] . '"AND password = "' . md5($_POST['password']) . '"';

            $result = mysqli_query($con,$sql) or die(mysql_error());
            
            if (!$result) {
                die("Error: " . mysqli_error($con));
            }

            if(mysqli_num_rows($result) < 1) {
                echo 'Password was probably incorrect!';
                exit();
            }

            $row = mysqli_fetch_array($result);

            $_SESSION["login"] = $hash;
            $_SESSION["userid"] = $row['id'];
            $_SESSION["username"] = $row['username'];
            $_SESSION["email"] = $row['email'];
            $_SESSION["avatar"] = $row['avatar'];

            mysqli_close($con);

            echo 'success';
            exit();
        }
    }

    if(isset($_GET['signup']))
    {
        $hash = md5('somethingSecret' . $_SERVER['HTTP_USER_AGENT']. session_id());

        $con = mysqli_connect("localhost","username","password","database");
    
        // Check connection
        if (mysqli_connect_errno()) {
            die("Failed to connect to MySQL: " . mysqli_connect_error());
        }

        if(isset($_POST['username'])) {
            if(empty($_POST['username'])) {
                echo "Empty username field!";
                exit();
            }
        } else {
            die('Error: No username param!');
        }

        if(isset($_POST['password'])) {
            if(empty($_POST['password'])) {
                echo "Empty password field!";
                exit();
            }
        } else {
            die('Error: No password param!');
        }

        if(isset($_POST['email'])) {
            if(empty($_POST['email'])) {
                echo "Empty email field!";
                exit();
            }
        } else {
            die('Error: No email param!');
        }

        $sql="INSERT INTO users (username, password, email, avatar) 
                VALUES ('$_POST[username]','" . md5($_POST['password']) . "','$_POST[email]','avatar.png')";

        //if ($error == "") {
            if (!mysqli_query($con,$sql)) {
                die('Error: ' . mysqli_error($con));
            }
        //}

        $_SESSION["login"] = $hash;
        $_SESSION["userid"] = mysqli_insert_id($con);
        $_SESSION["username"] = $_POST[username];

        mysqli_close($con);

        echo 'success';
        exit();
    }
 
    if(isset($_GET['insert']))
    {
        $hash = md5('somethingSecret' . $_SERVER['HTTP_USER_AGENT']. session_id());

        if (!(isset($_SESSION['login'])) || ($_SESSION['login'] != $hash)) {
            die('Error: You need to be logged!');
        }

        $con = mysqli_connect("localhost","username","password","database");
    
        // Check connection
        if (mysqli_connect_errno()) {
            die("Failed to connect to MySQL: " . mysqli_connect_error());
        }

        if(isset($_POST['honk'])) {
            if(empty($_POST['honk']))
                die('Error: Empty honk!');
        } else {
            die('Error: No honk param!');
        }

        if(isset($_POST['rehonk'])) {
            if(isset($_POST['userid'])) {
                if(empty($_POST['userid']))
                    die('Error: Empty userid!');
            } else {
                die('Error: No userid param!');
            }
            if($_POST['userid'] == $_SESSION['userid']) {
                die('Error: You can\'t rehonk your own honk!');
            }
            $sql="INSERT INTO honks (userid, honk, rehonk, timestamp) VALUES ('$_POST[userid]','$_POST[honk]','$_SESSION[userid]','". date('Y-m-d G:i:s',time()) ."')";
        } else {
            $sql="INSERT INTO honks (userid, honk, timestamp) VALUES ('$_SESSION[userid]','$_POST[honk]','". date('Y-m-d G:i:s',time()) ."')";
        }


        if ($error == "") {
            if (!mysqli_query($con,$sql)) {
                die('Error: ' . mysqli_error($con));
            }
        }

        mysqli_close($con);

        echo 'success!';

    }

    if(isset($_GET['getHonk']))
    {
        if(empty($_GET['getHonk']))
            die('Error: No id!');

        $con = mysqli_connect("localhost","username","password","database");
    
        // Check connection
        if (mysqli_connect_errno()) {
            $error .= "Failed to connect to MySQL: " . mysqli_connect_error() . "\n";
        }

        $sql = "SELECT * FROM honks WHERE id = " . $_GET['getHonk'];
        
        if (!($result = mysqli_query($con,$sql))) {
          die('Error: ' . mysqli_error($con));
        }

        $response = array();
            
        $row = mysqli_fetch_array($result);

        if($row > 1) {
            $time = strtotime($row['timestamp']);
            $time_since = humanTiming($time);

            $sql = "SELECT * FROM users WHERE id = ". $row['userid'];
            $result_user = mysqli_query($con,$sql);

            if (!$result_user) {
                die("Error: " . mysqli_error($con));
            }

            $row_user = mysqli_fetch_array($result_user);

            $response = array( 
              "userid" => $row['userid'],
              "username" => $row_user['username'],
              "avatar" => $row_user['avatar'],
              "time_since" => $time_since,
              "honk" => $row['honk'],
              "honkid" => $row['id']
            );
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    if(isset($_GET['getAllHonks']))
    {
        $con = mysqli_connect("localhost","username","password","database");
    
        // Check connection
        if (mysqli_connect_errno()) {
            $error .= "Failed to connect to MySQL: " . mysqli_connect_error() . "\n";
        }

        $sql = "SELECT * FROM honks ORDER BY timestamp DESC";
        
        if (!($result = mysqli_query($con,$sql))) {
          die('Error: ' . mysqli_error($con));
        }
            
        $items = array();

        while($row = mysqli_fetch_array($result)) {

            $time = strtotime($row['timestamp']);
            $time_since = humanTiming($time);

            $sql = "SELECT * FROM users WHERE id = ". $row['userid'];
            $result_user = mysqli_query($con,$sql);

            if (!$result_user) {
                die("Error: " . mysqli_error($con));
            }

            $row_user = mysqli_fetch_array($result_user);

            if($row['rehonk'] > 0) {
                $sql = "SELECT * FROM users WHERE id = ". $row['rehonk'];
                $result_rehonk = mysqli_query($con,$sql);

                if (!$result_rehonk) {
                    die("Error: " . mysqli_error($con));
                }

                $row_rehonk = mysqli_fetch_array($result_rehonk);
            }

            $item = array( 
              "userid" => $row['userid'],
              "username" => $row_user['username'],
              "avatar" => $row_user['avatar'],
              "time_since" => $time_since,
              "honk" => $row['honk'],
              "honkid" => $row['id']
            );

            if(isset($_SESSION['login'])) {
                $item['loggedin'] = 'true';
            }

            if($row['userid'] == $_SESSION['userid']) {
                $item['me'] = 'true';
            }

            if($row['rehonk'] > 0) {
                $item['rehonk'] = $row['rehonk'];
                $item['rehonkname'] = $row_rehonk['username'];
            }

            array_push($items, $item);

        }

        $response = array("honks" => $items);

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    if(isset($_GET['getMyHonks']))
    {
        $con = mysqli_connect("localhost","username","password","database");
    
        // Check connection
        if (mysqli_connect_errno()) {
            $error .= "Failed to connect to MySQL: " . mysqli_connect_error() . "\n";
        }

        $sql = "SELECT * FROM honks WHERE (userid = " . $_SESSION['userid'] . " AND rehonk = 0) OR (rehonk = " . $_SESSION['userid'] . ") ORDER BY timestamp DESC";
        
        if (!($result = mysqli_query($con,$sql))) {
          die('Error: ' . mysqli_error($con));
        }

        $items = array();
            
        while($row = mysqli_fetch_array($result)) {

            $time = strtotime($row['timestamp']);
            $time_since = humanTiming($time);

            $sql = "SELECT * FROM users WHERE id = ". $row['userid'];
            $result_user = mysqli_query($con,$sql);

            if (!$result_user) {
                die("Error: " . mysqli_error($con));
            }

            $row_user = mysqli_fetch_array($result_user);

            if($row['rehonk'] > 0) {
                $sql = "SELECT * FROM users WHERE id = ". $row['rehonk'];
                $result_rehonk = mysqli_query($con,$sql);

                if (!$result_rehonk) {
                    die("Error: " . mysqli_error($con));
                }

                $row_rehonk = mysqli_fetch_array($result_rehonk);
            }

            $item = array( 
              "userid" => $row['userid'],
              "username" => $row_user['username'],
              "avatar" => $row_user['avatar'],
              "time_since" => $time_since,
              "honk" => $row['honk'],
              "honkid" => $row['id']
            );

            if(isset($_SESSION['login'])) {
                $item['loggedin'] = 'true';
            }

            if($row['userid'] == $_SESSION['userid']) {
                $item['me'] = 'true';
            }

            if($row['rehonk'] > 0) {
                $item['rehonk'] = $row['rehonk'];
                $item['rehonkname'] = $row_rehonk['username'];
            }

            array_push($items, $item);
        }

        $response = array("honks" => $items);

        $response['username'] = $_SESSION['username'];

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    // TODO: The update method is currently not fully implemented
    if(isset($_GET['update'])){
        $error = '';

        $allowedExts = array("gif", "jpeg", "jpg", "png");
        $extension = end(explode(".", $_FILES["profilepic"]["name"]));
        if ((($_FILES["profilepic"]["type"] == "image/gif")
                || ($_FILES["profilepic"]["type"] == "image/jpeg")
                || ($_FILES["profilepic"]["type"] == "image/jpg")
                || ($_FILES["profilepic"]["type"] == "image/pjpeg")
                || ($_FILES["profilepic"]["type"] == "image/x-png")
                || ($_FILES["profilepic"]["type"] == "image/png"))
                && ($_FILES["profilepic"]["size"] < 2000000)
                && in_array($extension, $allowedExts))
        {
            if ($_FILES["profilepic"]["error"] > 0) {
                $error .= "Return Code: " . $_FILES["profilepic"]["error"] . "\n";
            } else {
               // echo "Upload: " . $_FILES["profilepic"]["name"] . "<br>";
               // echo "Type: " . $_FILES["profilepic"]["type"] . "<br>";
               // echo "Size: " . ($_FILES["profilepic"]["size"] / 1024) . " kB<br>";
               // echo "Temp file: " . $_FILES["profilepic"]["tmp_name"] . "<br>";

                if (file_exists("uploads/" . $_FILES["profilepic"]["name"])) {
                  //echo $_FILES["file"]["name"] . " already exists. " . "\n";
                } else {
                    move_uploaded_file($_FILES["profilepic"]["tmp_name"],
                    "uploads/" . $_FILES["profilepic"]["name"]);
                    //echo "Stored in: " . "uploads/" . $_FILES["profilepic"]["name"];
                }
            }
        } else {
          //echo "Invalid file";
          $error .= "Invalid file\n";
        }

        // TODO: Check if user is logged in!!!

        $con = mysqli_connect("localhost","username","password","database");
    
        // Check connection
        if (mysqli_connect_errno()) {
            $error .= "Failed to connect to MySQL: " . mysqli_connect_error() . "\n";
        }

        /*if(isset($_POST['honk'])) {
            if(empty($_POST['honk']))
                die('Error: Empty honk!');
        } else {
            die('Error: No honk param!');
        }*/

        $sql="UPDATE users SET avatar='http://example.com/uploaddir".$_FILES["profilepic"]["name"]."' WHERE id=" . $_SESSION['userid'];


        if ($error == "") {
            if (!mysqli_query($con,$sql)) {
                $error .= 'Error: ' . mysqli_error($con) . "\n";
            }
        }

        mysqli_close($con);

        if($error == "") {
            $response['status'] = 'success';
        } else {
            $response['status'] = 'error';
            $response['msg'] = $error;
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }
?>