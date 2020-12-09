<?php

$read = new \Conn\Read();
$read->exeRead("notifications_report", "WHERE notificacao = :idn", "idn={$dados['notificacao']}");
if(!$read->getResult()) {
    $del = new \Conn\Delete();
    $del->exeDelete("notifications", "WHERE id = :id", "id={$dados['notificacao']}");
}