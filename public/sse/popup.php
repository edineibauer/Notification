<?php

$data['data'] = [];
if(!empty($_SESSION['userlogin']) && $_SESSION['userlogin']['id'] > 0) {
    $sql = new \Conn\SqlCommand();
    $sql->exeCommand("SELECT * FROM " . PRE . "popup WHERE data_de_exibicao <= NOW() AND data_de_exibicao > '" . $_SESSION['userlogin']['data'] . "' AND (ownerpub = {$_SESSION['userlogin']['id']} OR (ownerpub IS NULL AND id NOT IN (SELECT popup FROM ".PRE."popup_user WHERE popup IS NOT NULL AND ownerpub = {$_SESSION['userlogin']['id']}))) ORDER BY data_de_exibicao DESC", !0, !0);
    if($sql->getResult()) {

        $del = new \Conn\Delete();
        $create = new \Conn\Create();

        foreach ($sql->getResult() as $i => $item) {
            if($i > 1) {

                if(!empty($item['ownerpub']))
                    $del->exeDelete("popup", "WHERE id = :idp", "idp={$item['id']}");
                else
                    $create->exeCreate("popup_user", ["popup" => $item['id'], "ownerpub" => $item['ownerpub']]);

                continue;
            }

            $item['imagem'] = !empty($item['imagem']) ? json_decode($item['imagem'], !0) : [];
            $data['data'][] = $item;
        }
    }
}