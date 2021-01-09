<?php

$read = new \Conn\Read(!0, !0);
$read->exeRead("enviar_popup");
if ($read->getResult()) {
    foreach ($read->getResult() as $dados) {
        if ($dados['enviado'] == 0 && !empty($dados['data_de_envio']) && $dados['data_de_envio'] <= date("Y-m-d H:i:s")) {

            $dadosPopup = [
                "titulo" => $dados['titulo'],
                "descricao" => $dados['descricao'],
                "imagem" => $dados['imagem']
            ];

            $create = new \Conn\Create();
            $create->exeCreate("popup", $dadosPopup);
            if ($create->getResult()) {
                $up = new \Conn\Update();
                $up->exeUpdate("enviar_popup", ["enviado" => 1], "WHERE id = :id", "id={$dados['id']}");
            }
        }
    }
}