<?php

namespace Notification;

use Conn\Create;
use Conn\SqlCommand;
use Google\Auth\CredentialsLoader;

class Notification
{

    /**
     * @param string $titulo
     * @param string $descricao
     * @param int|array $usuarios (ownerpub de 1 usuário (int), ou vários usuários (array)
     * @param string|null $dateTimeToShow
     */
    public static function popup(string $titulo, string $descricao, $usuarios = null, string $dateTimeToShow = null)
    {
        $create = new Create();
        if(!empty($usuarios) && is_array($usuarios)) {
            foreach ($usuarios as $usuario)
                $create->exeCreate("popup", ["titulo" => $titulo, "descricao" => $descricao, "data_de_exibicao" => $dateTimeToShow ?? date("Y-m-d H:i:s"), "ownerpub" => $usuario]);
        } else {
            $create->exeCreate("popup", ["titulo" => $titulo, "descricao" => $descricao, "data_de_exibicao" => $dateTimeToShow ?? date("Y-m-d H:i:s"), "ownerpub" => !empty($usuario) && is_numeric($usuario) ? ((int)$usuario) : null]);
        }
    }

    /**
     * @param string $titulo
     * @param string $descricao
     * @param int|array|null $usuarios (ownerpub de 1 usuário (int), ou vários usuários (array)
     * @param string|null $imagem
     * @return mixed|void|null
     */
    public static function push(string $titulo, string $descricao, $usuarios = null, string $imagem = null)
    {
        if (!defined('FB_SERVER_KEY') || empty(FB_SERVER_KEY) || (!empty($usuarios) && !is_array($usuarios) && !is_numeric($usuarios)))
            return null;

        /**
         * Obter endereço push FCM para enviar push
         */
        $tokens = [];
        if(!empty($usuarios)) {
            $sql = new SqlCommand();
            $sql->exeCommand("SELECT subscription FROM " . PRE . "push_notifications" . (!empty($usuarios) ? " WHERE usuario " . (is_array($usuarios) ? "IN (" . implode(", ", $usuarios) . ")" : "= {$usuarios}") : ""), !0, !0);
            if ($sql->getResult())
                $tokens = is_array($usuarios) ? array_map(fn($item) => $item['subscription'], $sql->getResult()) : $sql->getResult()[0]['subscription'];
        }

        return self::_privatePushSend($tokens, $titulo, $descricao, $imagem);

    }

    /**
     * @param array $tokens
     * @param string $title
     * @param string $body
     * @param string|null $image
     * @return mixed|void
     */
    private static function _privatePushSend(array $tokens, string $title, string $body, string $image = null)
    {
        if (!defined('FB_SERVER_KEY') || empty(FB_SERVER_KEY))
            return;

        $result = [];
        $credentials = CredentialsLoader::makeCredentials('https://www.googleapis.com/auth/firebase.messaging',
            json_decode(file_get_contents(PATH_HOME . '_config/firebase.json'), true));

        $tokenRequest = $credentials->fetchAuthToken();

        if(!empty($tokenRequest['access_token'])) {

            $headers = [
                "Authorization: Bearer " . $tokenRequest['access_token'],
                'Content-Type:application/json'
            ];

            $message = [
                "message" => [
                    "notification" => [
                        "title" => $title,
                        "body" => $body,
                        "image" => $image ?? ""
                    ]
                ]
            ];

            if(!empty($tokens)) {
                foreach ($tokens as $token) {
                    $message["message"]['token'] = $token;
                    $result[] = self::_sendRequestFirebasePush($message, $headers);
                }
            } else {
                $message["message"]["topic"] = "todos";
                $result[] = self::_sendRequestFirebasePush($message, $headers);
            }
        }

        return $result;
    }

    /**
     * @param array $message
     * @param array $headers
     * @return bool|string
     */
    private static function _sendRequestFirebasePush(array $message, array $headers)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/v1/projects/paygas-app/messages:send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}