<?php

$data['data'] = [];
if(!empty($_SESSION['userlogin']) && $_SESSION['userlogin']['id'] > 0) {
    $read = new \Conn\Read();
    $read->exeRead("popup", "WHERE data_de_exibicao <= NOW() ORDER BY data_de_exibicao DESC LIMIT 1");
    if($read->getResult()) {
        $item = $read->getResult()[0];

        $del = new \Conn\Delete();
        $del->exeDelete("popup", "WHERE id = :idp", "idp={$item['id']}");

        $item['imagem'] = !empty($item['imagem']) ? json_decode($item['imagem'], !0) : [];
        $data['data'] = $item;
    }
}