<?php
try {
  //フォームからの値をそれぞれ変数に代入
  $name = $_POST['name'];
  $mail = $_POST['mail'];
  $pass = password_hash($_POST['pass'], PASSWORD_DEFAULT);

  $host = 'localhost';
  $username = 'root';
  $passwd = 'root';
  $dbname = 'todo';

  // MySQLに接続する
  $mysqli = mysqli_connect($host, $username, $passwd, $dbname);

  // MySQLに接続できなかった場合はエラーを表示して終了
  if (!$mysqli) {
    throw new Exception('Connect error: ' . mysqli_connect_error());
  }
  mysqli_set_charset($mysqli, 'utf8mb4');

  // エラーメッセージを表示するように設定する
  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

  //フォームに入力されたmailがすでに登録されていないかチェック
  $query = "SELECT * FROM users WHERE mail = ? LIMIT 1";
  $stmt = mysqli_prepare($mysqli, $query);
  mysqli_stmt_bind_param($stmt, 's', $mail);
  mysqli_stmt_execute($stmt);

  $result = mysqli_stmt_get_result($stmt);

  // ユーザーが見つかった場合、同じメールアドレスが存在するため例外をスロー
  if (mysqli_num_rows($result) > 0) {
    throw new Exception('同じメールアドレスが存在します。');
  } else {
    // 登録されていなければinsert 
    $query = "INSERT INTO users(name, mail, pass) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($mysqli, $query);
    mysqli_stmt_bind_param($stmt, 'sss', $name, $mail, $pass);
    mysqli_stmt_execute($stmt);

    $msg = '会員登録が完了しました';
    $link = '<a href="login.php">ログインページ</a>';
  }

  // MySQLの接続を切断する
  mysqli_close($mysqli);
} catch (Exception $e) {
  $msg = $e->getMessage();
  $link = '<a href="signup.php">戻る</a>';
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
</head>
<body>
  <h1><?php echo $msg; ?></h1>
  <?php echo $link; ?>
</body>
</html>
