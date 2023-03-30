<?php
session_start();

// POSTリクエストの場合のみ処理を行う
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $mail = $_POST['mail'];
  $password = $_POST['pass'];

  $host = 'localhost';
  $username = 'root';
  $passwd = 'root';
  $dbname = 'todo';

  // MySQLに接続する
  $mysqli = mysqli_connect($host, $username, $passwd, $dbname);

  // MySQLに接続できなかった場合はエラーを表示して終了
  if (!$mysqli) {
    die('Connect error: ' . mysqli_connect_error());
  }
  mysqli_set_charset($mysqli, 'utf8mb4');

  // エラーメッセージを表示するように設定する
  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

  // フォームに入力されたmailをもとにユーザー情報を取得
  $query = "SELECT * FROM users WHERE mail = ? LIMIT 1";
  $stmt = mysqli_prepare($mysqli, $query);
  mysqli_stmt_bind_param($stmt, 's', $mail);
  mysqli_stmt_execute($stmt);

  $result = mysqli_stmt_get_result($stmt);

  // ユーザーが見つかった場合
  if (mysqli_num_rows($result) === 1) {
    $user = mysqli_fetch_assoc($result);

    // パスワードが正しい場合はログインする
    if (password_verify($password, $user['pass'])) {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['username'] = $user['name'];
      $_SESSION['mail'] = $user['mail'];
      header('Location: index.php');
      exit();
    }
  }

  // ログイン失敗時のエラーメッセージ
  $error_message = 'メールアドレスまたはパスワードが違います。';
}




?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="login-form">
    <h1>Todo ログイン</h1>
    <?php if (isset($error_message)) : ?>
      <p><?php echo $error_message; ?></p>
    <?php endif; ?>
    <form action="" method="post">
      <div class="usermail">
        <input type="email" name="mail" placeholder="メールアドレス" required>
      </div>
      <div class="userpass">        
        <input type="password" name="pass" placeholder="パスワード" required> 
      </div>
      <div class="userbutton">
        <input type="submit" value="ログイン">
      </div>
    </form>
    <p>アカウントをお持ちでない方は<a href="signup.php">こちら</a>から登録してください。</p>
  </div>
</body>
</html>
