<?php

$token = filter_input(INPUT_POST, 'tokenPush', FILTER_DEFAULT);
$code = filter_input(INPUT_POST, 'code', FILTER_DEFAULT);
$code = $code ?? "FCM";

if(!empty($_SESSION['userlogin']['id']) && $_SESSION['userlogin']['id'] > 0) {

    /**
     * Subscribe to topic todos
     */
    if (defined('FB_SERVER_KEY') && !empty(FB_SERVER_KEY)) {
        $headers = [
            "Authorization:key=" . FB_SERVER_KEY,
            'Content-Type:application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://iid.googleapis.com/iid/v1/' . $token . '/rel/topics/todos');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array());
        $result = curl_exec($ch);
        curl_close($ch);
    }

    /**
     * Registra FCM no servidor
     */
    $read = new \Conn\Read();
    $read->exeRead("push_notifications", "WHERE usuario = {$_SESSION['userlogin']['id']} AND code = '{$code}'", null, !0, !0, !0);
    if ($read->getResult()) {
        $up = new \Conn\Update();
        $up->exeUpdate("push_notifications", ["subscription" => $token], "WHERE id = :id", "id={$read->getResult()[0]['id']}");
    } else {
        $create = new \Conn\Create();
        $create->exeCreate("push_notifications", ["subscription" => $token, "usuario" => $_SESSION['userlogin']['id'], "code" => $code, "system_id" => null]);
    }
}