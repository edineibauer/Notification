<?php

$token = filter_input(INPUT_POST, 'tokenPush', FILTER_DEFAULT);
$code = filter_input(INPUT_POST, 'code', FILTER_DEFAULT);
$code = $code ?? "FCM";

$read = new \Conn\Read();
$read->exeRead("push_notifications", "WHERE usuario = {$_SESSION['userlogin']['id']} AND code = '{$code}'");
if($read->getResult()) {
    $up = new \Conn\Update();
    $up->exeUpdate("push_notifications", ["subscription" => $token], "WHERE id = :id", "id={$read->getResult()[0]['id']}");
} else {
    $create = new \Conn\Create();
    $create->exeCreate("push_notifications", ["subscription" => $token, "usuario" => $_SESSION['userlogin']['id'], "code" => $code, "system_id" => null]);
}