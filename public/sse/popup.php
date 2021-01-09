<?php

$data['data'] = [];
if(!empty($_SESSION['userlogin']) && $_SESSION['userlogin']['id'] > 0) {
    $sql = new \Conn\SqlCommand();
    $sql->exeCommand("SELECT * FROM " . PRE . "popup WHERE data_de_exibicao <= NOW() AND data_de_exibicao > '" . $_SESSION['userlogin']['data'] . "' AND (ownerpub = {$_SESSION['userlogin']['id']} OR (ownerpub IS NULL AND id NOT IN (SELECT popup FROM ".PRE."popup_user WHERE popup IS NOT NULL AND ownerpub = {$_SESSION['userlogin']['id']})))", !0, !0);
    if($sql->getResult()) {
        foreach ($sql->getResult() as $item) {
            $item['imagem'] = !empty($item['imagem']) ? json_decode($item['imagem'], !0) : [];
            $data['data'][] = $item;
        }
    }
}