<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

/**
 * 聊天主逻辑
 * 主要是处理 onMessage onClose
 */
use \GatewayWorker\Lib\Gateway;

class Events
{

   /**
    * 有消息时
    * @param int $client_id
    * @param mixed $message
    */
   public static function onMessage($client_id, $message)
   {
        // debug
        echo " client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']}",
             " gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}",
             " client_id:$client_id session:", json_encode($_SESSION),
             " onMessage:", $message, "\n";

        // 客户端传递的是json数据
        $message = json_decode($message, true);
        if (! $message) {
            return ;
        }

        // 根据类型执行不同的业务
        switch($message['type']) {
            // 客户端回应服务端的心跳
            case 'ping':
                break;
            // 客户端登录 message格式: {type:login, name:xx, room:1} ，添加到客户端，广播给所有客户端xx进入聊天室
            case 'login':
                // 判断是否有房间号
                if (! isset($message['room'])) {
                    throw new \Exception("\$message['room'] not set. client_ip:{$_SERVER['REMOTE_ADDR']} \$message:$message");
                }

                // 绑定客户端到UID
                $client_uid = ! empty($message['client_uid']) ? $message['client_uid'] : md5($message['client_name']);

                // 把房间号昵称放到session中，并将连接加入到当前组
                $room = $message['room'];
                $client_name = htmlspecialchars($message['client_name']);

                $newMessage = [
                    'type'        => $message['type'],
                    'scope'       => 'self',
                    'content_type'=> 'broadcast',
                    'client_uid'  => $client_uid,
                    'client_name' => $client_name,
                    'time'        => date('H:i')
                ];

                // 获取房间内所有在线用户，并发送给当前连接
                $clients_list = Gateway::getClientSessionsByGroup($room);
                $clientUidList = [];
                foreach ($clients_list as $item) {
                    $clientUidList[$item['client_uid']] = $item['client_name'];
                }
                $clientUidList[$client_uid] = $client_name;
                $newMessage['client_list'] = $clientUidList;
                Gateway::sendToCurrentClient(json_encode($newMessage));

                // 如果当前UID不在线，则广播给当前房间所有客户端，xx进入聊天室 message {type:login, client_id:xx, name:xx}
                if (! Gateway::isUidOnline($client_uid)) {
                    $newMessage['scope'] = 'all';
                    Gateway::sendToGroup($room, json_encode($newMessage));
                }

                $_SESSION['room'] = $room;
                $_SESSION['client_name'] = $client_name;
                $_SESSION['client_uid'] = $client_uid;
                Gateway::joinGroup($client_id, $room);
                Gateway::bindUid($client_id, $client_uid);

                break;

            // 客户端发言 message: {type:msg, to_client_id:xx, content:xx}
            case 'msg':
                // 非法请求
                if (! isset($_SESSION['room'])) {
                    throw new \Exception("\$_SESSION['room'] not set. client_ip:{$_SERVER['REMOTE_ADDR']}");
                }

                $newMessage = [
                    'type'             => 'msg',
                    'from_client_uid'  => $_SESSION['client_uid'],
                    'from_client_name' => $_SESSION['client_name'],
                    'to_client_uid'    => $message['to_client_uid'],
                    'content'          => '',
                    'content_type'     => 'msg',
                    'room'             => 0,
                    'time'             => date('H:i'),
                ];

                // 私聊
                if ('all' != $message['to_client_uid']) {
                    $clients = Gateway::getClientIdByUid($message['to_client_uid']);
                    if (empty($clients)) {
                        return Gateway::sendToCurrentClient(json_encode([
                            'type'   => 'msg',
                            'errmsg' => '对方已下线或者不存在'
                        ]));
                    }

                    $newMessage['content'] = "<b>对你说: </b>" . nl2br(htmlspecialchars($message['content']));
                    foreach ($clients as $clientID) {
                        Gateway::sendToClient($clientID, json_encode($newMessage));
                    }

                    Gateway::sendToCurrentClient(json_encode($newMessage));
                } else {
                    $newMessage['room'] = $_SESSION['room'];
                    $newMessage['content'] = $message['content'];

                    Gateway::sendToGroup($_SESSION['room'], json_encode($newMessage));
                }

                break;

            case 'rename':
                if (empty($message['client_uid']) || empty($message['client_name'])) {
                    throw new \Exception('client_uid not set.');
                }

                $oldName = $_SESSION['client_name'];
                $_SESSION['client_name'] = $message['client_name'];

                $newMessage = [
                    'type'         => 'rename',
                    'scope'        => 'all',
                    'content'      => sprintf('【%s】改名为【%s】。', $oldName, $_SESSION['client_name']),
                    'content_type' => 'broadcast',
                    'client_uid'   => $_SESSION['client_uid'],
                    'client_name'  => $_SESSION['client_name'],
                    'time'         => date('H:i')
                ];

                // 获取房间内所有在线用户，并发送给当前连接
                $clients_list = Gateway::getClientSessionsByGroup($_SESSION['room']);
                $clientUidList = [];
                foreach ($clients_list as $item) {
                    $clientUidList[$item['client_uid']] = $item['client_name'];
                }
                $clientUidList[$_SESSION['client_uid']] = $_SESSION['client_name'];

                $newMessage['client_list'] = $clientUidList;

                Gateway::sendToGroup($_SESSION['room'], json_encode($newMessage));

                break;
        }
   }

   /**
    * 当客户端断开连接时
    * @param integer $client_id 客户端id
    */
    public static function onClose($client_id)
    {
        // debug
        echo "client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:$client_id onClose:''\n";

        // 从房间的客户端列表中删除
        if (! isset($_SESSION['room'])) {
            return ;
        }

        // 如果是唯一连接，则广播该用户下线
        if (! $client_uid = $_SESSION['client_uid']) {
            return ;
        }

        if (Gateway::isUidOnline($client_uid)) {
            return ;
        }

        $newMessage = [
            'type'             => 'logout',
            'scope'            => 'all',
            'from_client_id'   => $client_id,
            'from_client_uid'  => $client_uid,
            'from_client_name' => $_SESSION['client_name'],
            'time'             => date('H:i')
        ];

        Gateway::sendToGroup($_SESSION['room'], json_encode($newMessage));
    }

}
