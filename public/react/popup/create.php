<?php

/**
 * Exclui todos os popups anteriores para este usuário
*/
$del = new \Conn\Delete();
$del->exeDelete("popup", "WHERE ownerpub = :o AND id != :id", "o={$dados['ownerpub']}&id={$dados['id']}");