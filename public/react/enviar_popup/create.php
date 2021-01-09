<?php

if (!empty($dados['data_de_envio']) && $dados['data_de_envio'] <= date("Y-m-d H:i:s")) {
    $dadosPopup = [
        "titulo" => $dados['titulo'],
        "descricao" => $dados['descricao'],
        "imagem" => $dados['imagem'],
        "data_de_exibicao" => date("Y-m-d H:i:s")
    ];

    if(!empty($dados['usuarios'])) {
        foreach ($dados['usuarios'] as $usuario) {
            $dadosPopup['ownerpub'] = $usuario;
            $create = new \Conn\Create();
            $create->exeCreate("popup", $dadosPopup);
        }
    } else {
        $create = new \Conn\Create();
        $create->exeCreate("popup", $dadosPopup);
    }

    /**
     * Seta enviar popup como enviado para não reenviar novamente
     */
    $up = new \Conn\Update();
    $up->exeUpdate("enviar_popup", ["enviado" => 1], "WHERE id = :id", "id={$dados['id']}");
}