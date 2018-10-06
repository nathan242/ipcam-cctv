<?php
    $page_no_login = true;
    require_once 'include/inc.main.php';
    
    if (!isset($_SESSION['user_id']) && (!isset($_POST['username']) || !isset($_POST['password']))) {
        $pagetitle = 'CCTV Login';
        require 'include/header.php';
    ?>
<div class="panel panel-default control-box login-panel">
  <div class="panel-heading" style="text-align: center;">CCTV Login</div>
  <form action="" method="POST">
    <table border="1">
      <tr>
        <td>Username:</td>
        <td style="width: 100%;"><input type="text" name="username" autocomplete="off" style="width: 100%;"></td>
      </tr>
      <tr>
        <td>Password:</td>
        <td style="width: 100%;"><input type="password" name="password" style="width: 100%;"></td>
      </tr>
      <tr>
        <td colspan="2"><input class="btn" type="submit" value="Login" style="width: 100%;"></td>
      </tr>
    </table>
  </form>
</div>
<?php
    require 'include/footer.php';
    } elseif (!isset($_SESSION['user_id']) && isset($_POST['username']) && isset($_POST['password'])) {
        if (user::login($db, $_POST['username'], $_POST['password'])) {
            header('Location: cctv.php');
        } else {
            $pagetitle = 'Login Failed!';
            require 'include/header.php';
            echo '<p>ERROR: Unknown username or password.</p>';
            echo '<p><a href="index.php">Back to login page</a></p>';
            require 'include/footer.php';
        }
    } else {
        // Already logged in
        header('Location: cctv.php');
    }
