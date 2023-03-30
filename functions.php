<?php

require_once('config.php');

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

function addTodo($mysqli)
{
  $title = trim(filter_input(INPUT_POST, 'title'));
  $deadline = filter_input(INPUT_POST, 'deadline');

  // 日時のフォーマットを変換する
  $deadline = formatDatetime($deadline);

  if ($title === '') {
    return;
  }

  // セッションからユーザーIDを取得する
  $user_id = $_SESSION['user_id'];
  
  // タスクを追加する
  $stmt = mysqli_prepare($mysqli, "INSERT INTO todos (title, deadline, user_id) VALUES (?, ?, ?)");
  mysqli_stmt_bind_param($stmt, 'ssi', $title, $deadline, $user_id);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);
}



function toggleTodo($mysqli)
{
  $id = filter_input(INPUT_POST, 'id');
  if (empty($id)) {
    return;
  }

  $query = "UPDATE todos SET is_done = NOT is_done WHERE id = ?";
  $stmt = mysqli_prepare($mysqli, $query);
  mysqli_stmt_bind_param($stmt, 'i', $id);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);
}


function deleteTodo($mysqli)
{
  $id = filter_input(INPUT_POST, 'id');
  if (empty($id)) {
    return;
  }

  $query = "DELETE FROM todos WHERE id = ?";
  $stmt = mysqli_prepare($mysqli, $query);
  mysqli_stmt_bind_param($stmt, 'i', $id);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);
}


function purge($mysqli) {
  $query = "DELETE FROM todos WHERE is_done = 1";
  mysqli_query($mysqli, $query);
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
