<?php

namespace Dashboard;

use Conn\SqlCommand;

class Notification
{
    /**
     * @param $usuarios (ownerpub de 1 usuário (int), ou vários usuários (array)
     * @param string $titulo
     * @param string $descricao
     * @param string|null $imagem
     * @return mixed|void|null
     */
    public static function push($usuarios, string $titulo, string $descricao, string $imagem = null)
    {
        if (!defined('FB_SERVER_KEY') || empty(FB_SERVER_KEY) || (!is_array($usuarios) && !is_numeric($usuarios)))
            return null;

        /**
         * Obter endereço push FCM para enviar push
         */
        $sql = new SqlCommand();
        $sql->exeCommand("SELECT subscription FROM " . PRE . "push_notifications WHERE usuario " . is_array($usuarios) ? "IN (" . implode(", ", $usuarios) . ")" : "= {$usuarios}");
        if ($sql->getResult()) {
            $token = is_array($usuarios) ? array_map(fn($item) => $item['subscription'], $sql->getResult()) : $sql->getResult()[0]['subscription'];
            return self::_privatePushSend($token, $titulo, $descricao, $imagem);
        }

        return null;
    }

    /**
     * @param $target
     * @param string $title
     * @param string $body
     * @param string|null $image
     * @return mixed|void
     */
    private static function _privatePushSend($target, string $title, string $body, string $image = null)
    {
        if (!defined('FB_SERVER_KEY') || empty(FB_SERVER_KEY))
            return;

        $message = [
            "notification" => [
                "title" => $title,
                "body" => $body,
                "icon" => $image ?? "",
                "click_action" => "FCM_PLUGIN_ACTIVITY"
            ],
            "priority" => "high"
        ];
        $message[is_string($target) ? 'to' : 'registration_ids'] = $target;

        $headers = [
            "Authorization:key=" . FB_SERVER_KEY,
            'Content-Type:application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, !0);
    }
}