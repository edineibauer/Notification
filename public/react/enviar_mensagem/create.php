<?php

$usuarios = [];
$emails = [];

$mensagem = $dados;
$mensagem['imagem'] = (!empty($mensagem['imagem']) ? json_decode($mensagem['imagem'], !0)[0]['url'] : "");
$mensagem['canais'] = json_decode($mensagem['canais'], !0);

if (!empty($mensagem['enviar_para_relatorios'])) {
    $relatorios = json_decode($mensagem['enviar_para_relatorios'], !0);

    if (!empty($relatorios)) {
        foreach ($relatorios as $relatorio) {
            $report = new \Report\Report((int)$relatorio);
            $reportData = $report->getReport();

            if (!empty($report->getResult())) {
                foreach ($report->getResult() as $result) {
                    $dicionario = \Entity\Metadados::getDicionario($reportData['entidade']);
                    $isIdUsuarioInReport = $reportData['entidade'] === $mensagem['enviar_para'];

                    /**
                     * Encontra a coluna
                     */
                    $column = "";
                    if ($isIdUsuarioInReport) {
                        $column = "usuarios_id";
                    } else {
                        foreach ($dicionario as $item) {
                            if ($item['relation'] === $mensagem['enviar_para']) {
                                $column = $item['column'];
                                break;
                            }
                        }
                    }

                    /**
                     * Encontra email
                     */
                    $email = "";
                    if (in_array("2", $mensagem['canais'])) {
                        foreach ($dicionario as $dic) {
                            if ($dic['format'] === "email") {
                                $email = $dic['column'];
                                break;
                            }
                        }
                        if ($email === "") {
                            foreach (\Entity\Metadados::getDicionario($mensagem['enviar_para']) as $dic) {
                                if ($dic['format'] === "email") {
                                    $email = $dic['column'];
                                    break;
                                }
                            }
                        }
                    }

                    if (!empty($column)) {
                        $sql = new \Conn\SqlCommand();
                        if ($report->getResult()) {

                            /**
                             * Obtém lista de ids
                             */
                            $ids = [];
                            foreach ($report->getResult() as $r) {
                                $ids[] = (int)$r[$column];

                                //email
                                if ($isIdUsuarioInReport && $email !== "" && !empty($r[$email]) && !in_array($r[$email], $emails))
                                    $emails[(int) $r[$column]] = $r[$email];
                            }

                            if (!empty($ids)) {

                                /**
                                 * Convert lista de ids de setor em lista de ids de usuário
                                 */
                                if (!$isIdUsuarioInReport) {
                                    $sql->exeCommand("SELECT usuarios_id" . ($email !== "" ? ", {$email}" : "") . " FROM " . PRE . $mensagem['enviar_para'] . " WHERE id IN (" . implode(',', $ids) . ")");
                                    if ($sql->getResult()) {
                                        foreach ($sql->getResult() as $item) {
                                            if (!in_array($item['usuarios_id'], $usuarios))
                                                $usuarios[] = (int) $item['usuarios_id'];

                                            //email
                                            if ($email !== "" && !empty($item[$email]) && !in_array($item[$email], $emails))
                                                $emails[$item['usuarios_id']] = $item[$email];
                                        }
                                    }

                                } else {
                                    $usuarios = $ids;
                                }
                            }
                        }

                        /**
                         * Atualiza total de mensagens enviada em Enviar Mensagem
                         */
                        $up = new \Conn\Update();
                        $up->exeUpdate("enviar_mensagem", ["total_de_envios" => count($usuarios)], "WHERE id = :mid", "mid={$mensagem['id']}");

                        if (!empty($usuarios)) {

                            /**
                             * push notification
                             */
                            if (in_array("1", $mensagem['canais'])) {

                                /**
                                 * Para cada usuário, envia a mensagem
                                 */
                                $note = new \Dashboard\Notification();
                                $note->setTitulo($mensagem['assunto']);
                                $note->setDescricao($mensagem['descricao']);
                                $note->setImagem($mensagem['imagem']);
                                $note->setEnviarMensagemAssociation($mensagem['id']);

                                if (!empty($mensagem['url']))
                                    $note->setUrl($mensagem['url']);

                                $note->setUsuarios($usuarios);
                                $note->enviar();
                            }

                            /**
                             * Email notification
                             */
                            if (in_array("2", $mensagem['canais'])) {
                                foreach ($emails as $email) {
                                    $emailEnvio = new \Email\EmailEnvio();
                                    $emailEnvio->setAssunto($mensagem['assunto']);
                                    $emailEnvio->setMensagem($mensagem['descricao']);
                                    $emailEnvio->setDataEnvio($mensagem['data_de_envio']);
                                    $emailEnvio->setDestinatarioEmail($email);
                                    $emailEnvio->enviar();
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}