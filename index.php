<?php

require_once('config.php');
require_once('functions.php');

// ログインチェック処理
if (!isset($_SESSION['username'])) {
  // ログインしていない場合はログインページにリダイレクトする
  header('Location: login.php');
  exit;
}

createToken();


$mysqli = mysqli_connect($host, $username, $passwd, $dbname);
if (!$mysqli) {
  die('Connect error: ' . mysqli_connect_error());
}
mysqli_set_charset($mysqli, 'utf8mb4');

// タイムゾーンをローカルタイムゾーンに設定する
mysqli_query($mysqli, "SET time_zone = '+09:00'");

// エラーメッセージを表示するように設定する
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  validateToken();
  $action = filter_input(INPUT_GET, 'action');

  switch ($action) {
    case 'add':
      addTodo($mysqli);
      break;
    case 'toggle':
      toggleTodo($mysqli);
      break;
    case 'delete':
      deleteTodo($mysqli);
      break;
    case 'purge':
      purge($mysqli);
      break;
    default:
    exit;
    }

  header('Location: ' . SITE_URL);
  exit;
}


$todos = getTodos($mysqli);

?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Todo</title>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.css">
  <link rel="stylesheet" href="styles.css">
</head>

<body>
  <div class="main">
      <div class="user-info">
        <span class="user-name"><?php echo h($_SESSION['username']); ?>さん、ようこそ！</span>
        <form action="logout.php" method="post">
          <button type="submit" class="logout-btn">ログアウト</button>
          <input type="hidden" name="token" value="<?php echo h($_SESSION['token']); ?>">
        </form>
      </div>

  <div class="header">
    <h1>Todoリスト</h1>
    <form action="?action=purge" method="post">
      <span class="purge">一括削除</span>
      <input type="hidden" name="token" value="<?= h($_SESSION['token']); ?>">
    </form>
  </div>

  <form method="post" action="?action=add">
    <div class="todo-title">
      <label for="title">タイトル:</label>
      <input type="text" id="title" name="title" placeholder="Todoを入力してみよう">
    </div>
    <div class="todo-deadline">
      <label for="deadline">期　　日:</label>
      <input type="text" id="deadline" name="deadline" placeholder="日付と時間を選択してください">

      <button type="submit">追加</button>
      <input type="hidden" name="token" value="<?= h($_SESSION['token']); ?>">
    </div>
  </form>
  


      <ul>
      <?php foreach ($todos as $todo): ?>
        <li>
        <form action="?action=toggle" method="post">
            <input type="checkbox" <?= $todo['is_done'] ? 'checked' : ''; ?>>
            <input type="hidden" name="id" value="<?= h($todo['id']); ?>">
            <input type="hidden" name="token" value="<?= h($_SESSION['token']); ?>">
         </form>

          <!-- <span class="<?= $todo['is_done'] ? 'done' : ''; ?>">
          <?= h($todo['title']); ?> -->

          <span class="title <?= $todo['is_done'] ? 'done' : ''; ?>">
          <?= h($todo['title']); ?>
          </span>

          <?php $deadline = formatDatetime($todo['deadline']); ?>
          
          <?php if ($deadline === '1970-01-01 00:00'): ?>
          <?= '<span class="deadline">期日: 未定</span>'; ?>
          <?php else: ?>
          <?= '<span class="deadline">期日: ' . h($deadline) . '</span>'; ?>
          <?php endif; ?>
          
          </span>

        <form action="?action=delete" method="post" class="delete-form">
            <span class="delete">削除</span>
            <input type="hidden" name="id" value="<?= h($todo['id']); ?>">
            <input type="hidden" name="token" value="<?= h($_SESSION['token']); ?>">
         </form>

        </li>
      <?php endforeach; ?>
      </ul>      
      <a href="calendar.php">
        <i class="fas fa-calendar-alt"></i> カレンダーへ
      </a>
      </div>

    
     
      <script>
          'use strict';
    {
      const checkboxes = document.querySelectorAll('input[type="checkbox"]');
      checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', () => {
          checkbox.parentNode.submit();
        });
      });

      const deletes = document.querySelectorAll('.delete');
      deletes.forEach(span => {
        span.addEventListener('click', () => {
          if (!confirm('削除しますか?')) {
        return;
        }
          span.parentNode.submit();
        });
      });

      const purge = document.querySelector('.purge');
      purge.addEventListener('click', () => {
        if (!confirm('削除しますか?')) {
          return;
        }
        purge.parentNode.submit();
      });

      
    $(function() {
      $("#deadline").datetimepicker({
        dateFormat: "yy-mm-dd",
        timeFormat: "HH:mm",
        changeMonth: true,
        changeYear: true,
        yearRange: "-100:+10"
      });
    });
 

    }
        </script>
  </body>
</html>