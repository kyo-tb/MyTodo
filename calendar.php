<?php

session_start();

// データベースへの接続に必要な変数を指定
$host = 'localhost';
$username = 'root';
$passwd = 'root';
$dbname = 'todo';

define('SITE_URL', 'http://localhost:8888/work/calendar.php');

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


function h($s) {
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}


function createToken()
{
  if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
  }
}


function formatDatetime($datetime) {
   return date('Y-m-d H:i', strtotime(str_replace('T', ' ', $datetime)));
}


function validateToken()
{
  if (
    empty($_SESSION['token']) ||
    $_SESSION['token'] !== filter_input(INPUT_POST, 'token')
  ) {
    exit('Invalid post request');
  }
}


function getTodos($mysqli)
{
  // ログインしたユーザーのidを取得する
  $user_id = $_SESSION['user_id'];


  // 期日が未定のものを取得するためのクエリを作成する
  $query = "SELECT * FROM todos WHERE deadline = '1970-01-01 00:00:00' AND user_id = ? ORDER BY id DESC";

  // 期日が未定のものを取得する
  $stmt = mysqli_prepare($mysqli, $query);
  mysqli_stmt_bind_param($stmt, 's', $_SESSION['user_id']);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $no_deadline_todos = mysqli_fetch_all($result, MYSQLI_ASSOC);

  // 期日があるものを取得するためのクエリを作成する
  $query = "SELECT * FROM todos WHERE deadline != '1970-01-01 00:00:00' AND user_id = ? ORDER BY deadline ASC";

  // 期日があるものを取得する
  $stmt = mysqli_prepare($mysqli, $query);
  mysqli_stmt_bind_param($stmt, 's', $_SESSION['user_id']);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $deadline_todos = mysqli_fetch_all($result, MYSQLI_ASSOC);
  mysqli_stmt_close($stmt);

  // 期日が未定のものを先に表示するため、配列を結合する
  $todos = array_merge($no_deadline_todos, $deadline_todos);

  return $todos;
}


$todos = getTodos($mysqli);

// カレンダー表示
// 現在の年月を取得
if (isset($_GET['year']) && isset($_GET['month'])) {
  $year = $_GET['year'];
  $month = $_GET['month'];
} else {
  $year = date('Y');
  $month = date('n');
}

// $year = date('Y');
// $month = date('n');

 
// 月末日を取得
$last_day = date('j', mktime(0, 0, 0, $month + 1, 0, $year));
 
$calendar = array();
$j = 0;
 
// 月末日までループ
for ($i = 1; $i < $last_day + 1; $i++) {
 
    // 曜日を取得
    $week = date('w', mktime(0, 0, 0, $month, $i, $year));
 
    // 1日の場合
    if ($i == 1) {
 
        // 1日目の曜日までをループ
        for ($s = 1; $s <= $week; $s++) {
 
            // 前半に空文字をセット
            $calendar[$j]['day'] = '';
            $j++;
 
        }
 
    }
 
    // 配列に日付をセット
    $calendar[$j]['day'] = $i;
    $j++;
 
    // 月末日の場合
    if ($i == $last_day) {
 
        // 月末日から残りをループ
        for ($e = 1; $e <= 6 - $week; $e++) {
 
            // 後半に空文字をセット
            $calendar[$j]['day'] = '';
            $j++;
 
        }
 
    }
 
}

// 前月、次月の年月を取得する
$prev_year = $year;
$prev_month = $month - 1;
if ($prev_month == 0) {
    $prev_year--;
    $prev_month = 12;
}

$next_year = $year;
$next_month = $month + 1;
if ($next_month == 13) {
    $next_year++;
    $next_month = 1;
}


// 前月、次月のURLを生成する
// $prev_url = 'calendar.php?year=' . $prev_year . '&month=' . $prev_month;
// $next_url = 'calendar.php?year=' . $next_year . '&month=' . $next_month;

$prev_url = SITE_URL . '?year=' . $prev_year . '&month=' . $prev_month;
$next_url = SITE_URL . '?year=' . $next_year . '&month=' . $next_month;


 
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Calendar</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    table {
      flex-grow: 1;
      width: 80%;
      table-layout: fixed;
      margin: auto; /* テーブルを中央揃え */
      border-collapse: collapse; /* テーブルの枠線を1本にする */
    }


    table th {
      background: #EEEEEE;
      height: 48px;
    }

    table th,
    table td {
      border: 1px solid #CCCCCC;
      text-align: center;
      padding: 5px;
      width: 14.28%; /* 1週間分のセル幅を均等に配分 */
    }

    td {
      /* height: calc(100% / 6); 6は週の数に基づいて調整 */
      vertical-align: top; /* 日付を上揃えにする */
    }

    /* カレンダーセル内のタイトルのスタイル */
    td span {
        display: inline-block;
        width: 100%;
        font-size: small;
        text-align: left;
        padding-left: 5px;
      }

  .cal-body {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 400px;
}

.home {
  width: 80%;
  margin-top: 16px;
  text-align: left;
}

  </style>
  
</head>

<body>
<div class="cal-header">
  <h1><?php echo $year; ?>年<?php echo $month; ?>月のカレンダー</h1>
  <!-- 前月、次月のボタンを表示する -->
  <p><a href="<?php echo $prev_url; ?>">前月</a> | <a href="<?php echo $next_url; ?>">次月</a></p>
</div>
  <div class="cal-body">
    <table>
        <tr>
            <th>日</th>
            <th>月</th>
            <th>火</th>
            <th>水</th>
            <th>木</th>
            <th>金</th>
            <th>土</th>
        </tr>
     
        <tr>
        <?php $cnt = 0; foreach ($calendar as $key => $value): ?>
            <?php if ($cnt == 0): ?>
            <tr>
            <?php endif; ?>   
            <td>
            <?php $cnt++; echo $value['day']; ?>
     
            <?php foreach ($todos as $todo): ?>
                <?php if ($todo['deadline'] && substr($todo['deadline'], 0, 10) === date('Y-m', strtotime($year . '-' . $month)) . '-' . sprintf('%02d', $value['day'])): ?>
                    <br><?php echo '<span>' . h($todo['title']) . '</span>'; ?>                 
  
                <?php endif; ?>
            <?php endforeach; ?>
            </td>
     
        <?php if ($cnt == 7): ?>
          </tr>
        <?php $cnt = 0; endif; ?>
     
        <?php endforeach; ?>
        <?php if ($cnt != 0): ?>
          <?php for ($i = $cnt; $i < 7; $i++): ?>
            <td></td>
          <?php endfor; ?>
            </tr>
        <?php endif; ?>
    </table>

    <div class="home">
      <a href="index.php"> Todoへ</a>
    </div>
  </div>


  <script>

  </script>
  </body>
</html>