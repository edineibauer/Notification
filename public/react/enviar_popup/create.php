<?php

if (!empty($dados['usuarios'])) {
    $dadosPopup = [
        "titulo" => $dados['titulo'],
        "descricao" => $dados['descricao'],
        "imagem" => $dados['imagem'],
        "data_de_exibicao" => date("Y-m-d H:i:s")
    ];

    $create = new \Conn\Create();
    foreach ($dados['usuarios'] as $usuario) {
        $dadosPopup['ownerpub'] = $usuario;
        $create->exeCreate("popup", $dadosPopup);
    }

} else {

    /**
     * Exclui todos os popups antes de criar uma nova demanda
     * Apenas um popup por usuário
     */
    $del = new \Conn\Delete();
    $del->exeDelete("popup", "WHERE id > 0");

    /**
     * Lê todos os clientes para adicionar um popup para cada um
     */
    $read = new \Conn\Read(!0, !0);
    $read->exeRead("usuarios", "WHERE status = 1");
    if ($read->getResult()) {
        $sql_comando = "INSERT INTO " . PRE . "popup ('titulo', 'descricao', 'imagem', 'ownerpub', 'data_de_exibicao') VALUES ";

        foreach ($read->getResult() as $i => $item)
            $sql_comando .= ($i > 0 ? ", " : "") . "('{$dados['titulo']}', '{$dados['descricao']}', {$dados['imagem']}, {$item['id']}, '{$dados['data_de_envio']}')";

        $sql = new \Conn\SqlCommand(!0, !0);
        $sql->exeCommand($sql_comando . ";");
    }
}
