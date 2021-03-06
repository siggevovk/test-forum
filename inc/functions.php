<?php
if ($_SERVER['REQUEST_URI'] === '/____/inc/functions.php') {
  header('Location:/____/');
}

function userIsExist($username){
  global $conn;

  $query = "SELECT * FROM user WHERE username = '$username' ";
  $result = mysqli_query($conn, $query);

  if (mysqli_num_rows($result) > 0) {
    return true;
  }
  else {
    return false;
  }
}

function RegistrateUser(){
  global $conn;

  $username = $_POST['username'];
  $password = $_POST['password'];

  $hashFormat = "$2y$10$";
  $salt = "BVvbnrweuyigbfhbLFbgoF";

  $hashAndSalt = $hashFormat . $salt;
  $password = crypt($password, $hashAndSalt);

  if (userIsExist($username)) {
    return $taken = "Username is Taken!";
  }
  else {
    $username = mysqli_real_escape_string($conn, $username);
    $password = mysqli_real_escape_string($conn, $password);

    $query = "INSERT INTO user(username, password) ";
    $query .= "VALUES ('$username', '$password')";

    $result = mysqli_query($conn, $query);

    if (!$result) {
      die("Query failed") . mysqli_error($conn);
    }
    header("Location: login.php");
  }
}

function LoginUser(){
  global $conn;

  $db_user = '';
  $db_pass = '';

  $username = $_POST['username'];
  $password = $_POST['password'];

  $username = mysqli_real_escape_string($conn, $username);
  $password = mysqli_real_escape_string($conn, $password);

  $query = "SELECT * FROM user WHERE username = '{$username}'";
  $select_user_query = mysqli_query($conn, $query);

  if(!$select_user_query){
    die('Query Failed') . mysqli_error($conn);
  }
  while($row = mysqli_fetch_array($select_user_query)){
    $db_ID = $row['id'];
    $db_user = $row['username'];
    $db_pass = $row['password'];
  }

  $password = crypt($password, $db_pass);

  if($username === $db_user && $password === $db_pass){
    $_SESSION['id'] = $db_ID;
    $_SESSION['username'] = $db_user;
    $_SESSION['password'] = $db_pass;
    header('location:/____/');
  }else{
    echo 'Wrong Username / Password!'. mysqli_error($conn);
  }
}

function time_elapsed_string($timestamp) {
  date_default_timezone_set("Europe/Helsinki");
  $time_ago        = strtotime($timestamp);
  $current_time    = time();
  $time_difference = $current_time - $time_ago;
  $seconds         = $time_difference;

  $minutes = round($seconds / 60); // value 60 is seconds
  $hours   = round($seconds / 3600); //value 3600 is 60 minutes * 60 sec
  $days    = round($seconds / 86400); //86400 = 24 * 60 * 60;
  $weeks   = round($seconds / 604800); // 7*24*60*60;
  $months  = round($seconds / 2629440); //((365+365+365+365+366)/5/12)*24*60*60
  $years   = round($seconds / 31553280); //(365+365+365+365+366)/5 * 24 * 60 * 60

  //Seconds, Minutes, Hours, Weeks, Months, Years
    if ($seconds <= 60){
      return "Just Now";
    } else if ($minutes <= 60){
      if ($minutes == 1){
        return "one minute ago";
      } else {
        return "$minutes minutes ago";
      }
    } else if ($hours <= 24){
      if ($hours == 1){
        return "an hour ago";
      } else {
        return "$hours hrs ago";
      }

    } else if ($days <= 7){
      if ($days == 1){
        return "yesterday";
      } else {
        return "$days days ago";
      }

    } else if ($weeks <= 4.3){
      if ($weeks == 1){
        return "a week ago";
      } else {
        return "$weeks weeks ago";
      }

    } else if ($months <= 12){
      if ($months == 1){
        return "a month ago";
      } else {
        return "$months months ago";
      }

    } else {
      if ($years == 1){
        return "one year ago";
      } else {
        return "$years years ago";
      }
    }
}

function submitPost(){
  global $conn;

  $title = $_GET['title'];
  $textarea = $_REQUEST['text_msg'];
  $users_id = $_SESSION['id'];
  $date_posted = date("Y-m-d H:i:s");

  $title = mysqli_real_escape_string($conn, $title);
  $textarea = mysqli_real_escape_string($conn, $textarea);
  $users_id = mysqli_real_escape_string($conn, $users_id);
  $date_posted = mysqli_real_escape_string($conn, $date_posted);

  $query = "INSERT INTO posts( id ,title, post_content, created, user_id) VALUES('','$title','$textarea','$date_posted','$users_id')";

  $result = mysqli_query($conn, $query);

  if (!$result) {
    die('failed') . mysqli_error($conn);
  }
}

function displayPost(){
  global $conn;

  $query = "SELECT * FROM posts ORDER BY created DESC";
  $result = mysqli_query($conn,$query);

  while($row = mysqli_fetch_assoc($result)){
    echo "
    <a href='details.php?ID=". $row['id']."-". $row['user_id'] ."'>
    <div class='post_container' style=''>
    <h3 class='title_class'>" . $row['title'] . "</h3>
    <p class='content_class'>". $row['post_content'] ."
    <div class='date_n_name'><h5>". time_elapsed_string($row['created']) ."</h5></div>
    </div>
    </a>
    ";
  }
}

function AddCommentToDB(){
  global $conn;

  $comment = $_REQUEST['comment'];
  $users_id = $_SESSION['username'];
  $post_ID = $_GET['ID'];

  $comment = mysqli_real_escape_string($conn, $comment);
  $users_id = mysqli_real_escape_string($conn, $users_id);
  $post_ID = mysqli_real_escape_string($conn, $post_ID);

  $test = NULL;

  $query = "INSERT INTO comments_2(post_id, comment, parent_id, user) VALUES('$post_ID', '$comment', '$test', '$users_id')";

  $result = mysqli_query($conn, $query);

  if (!$result) {
    $result = mysqli_error($conn);
    echo $result;
  }
}

function AddReplyToDB(){
  global $conn;

  $comment = $_REQUEST['reply_text'];
  $users_id = $_SESSION['username'];
  $post_ID = $_GET['ID'];
  $reply_id = $_REQUEST['data'];

  $comment = mysqli_real_escape_string($conn, $comment);
  $users_id = mysqli_real_escape_string($conn, $users_id);
  $post_ID = mysqli_real_escape_string($conn, $post_ID);


  $reply_query = "INSERT INTO comments_2(post_id, comment, parent_id, user) VALUES('$post_ID',  '$comment', '$reply_id', '$users_id')";

  $res = mysqli_query($conn, $reply_query);

  if (!$res) {
    $res = mysqli_error($conn);
    echo $res;
  }
}

//comments tree 2.0
function comment_tree($parrent){
  global $conn;
  $ID = $_GET['ID'];
  $sql = "SELECT * FROM comments_2 WHERE post_id = '$ID' AND parent_id='$parrent'";
  $result = $conn->query($sql);
  $row_count = $result->num_rows;

  while($row = mysqli_fetch_object($result)){
    $i = 0;
    if ($row->parent_id == 0) {
      echo "<div class='comment-border'>";
    }
    if ($i == 0){

    echo '
    <div class="helloworld"><ul style="list-style:none;padding-left:20px;">';
    if ($row->parent_id != 0) {
        echo "<div class='child' id='childID'>";
      }
      echo ' <p class="author_name">'.$row->user.'</p>
          <li><h4 id="comm_txt" style="font-size:20px;">'. $row->comment.'</h4>
            <div name="testdiv" id="formCont" style="margin-bottom:10px;" class="form-container">
              <form id="reply-form '.uniqid(uniqid()).'" method="post" class="reply_form">
                <input type="hidden" name="data" value='.$row->id.'>
                <textarea name="reply_text" id="'.uniqid(uniqid()).' " class="reply_texta" col="30" rows="10" style="width:150px;height:30px;"></textarea>
                <button name="replyy" class="reply" id="'.uniqid(uniqid()).'">Send</button>
              </form>
            <a id="'.uniqid().'" style="position:absolute;' .($row->parent_id ? 'margin:-28px 0 0 40px;' : 'margin:-28px 0 0 100px;').' left:auto;" class="Areply">Reply</a>';
            if($row->parent_id == 0){
              echo '<a class="'.uniqid().'" style="position:absolute;margin: -28px 0 0 48px;" id="collapsDemo"></a>';
            }
          echo '</div>';


      if ($row->parent_id != 0) {
          echo "</div>";
        }
        comment_tree($row->id);
        echo '</li>';
        $i++;
     }
     if ($i > 0) {
       echo '</ul>';
       echo '</div>';
     }
     if ($row->parent_id == 0) {
       echo "</div>";
     }
  }
}
?>
