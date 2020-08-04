<?php
// 创建server对象，监听127.0.0.1:9501端口，默认是多进程模式，socket_type 默认是tcp
$serv = new Swoole\Server('127.0.0.1', 9501);

// 必须再$serv ->start之前调用
$serv->set([
    'worker_num' => 8, // worker进程数 开启cpu核数的1-4倍
    'max_request' => 100,
]);

/**
 * $serv ->on(string $event, mixed $callback)
 * $event :
 *  connect 当建立连接
 *  receive 当接收到数据
 *  close 关闭连接
 * $serv 服务器信息
 * $fd 是客户端连接的唯一标识
 * $reactor_id线程id
 */
$serv->on('Connect', function ($serv, $fd, $reactor_id) {
    echo "Client{$fd}:Connect";
});

/**
 * 监听数据接收事件
 * $serv 服务器信息
 * $fd 客户端连接的唯一标识
 * $reactor_id线程id
 * $data 接收到的数据
 */
$serv->on('receive', function ($serv, $fd, $reactor_id, $data) {
    $serv->send($fd, "send data" . $data . 'to' . $reactor_id);
});

/**
 * 监听连接关闭事件
 * $serv 服务器信息
 * $fd 客户端连接的唯一标识
 */
$serv->on('close', function ($serv, $fd) {
    echo 'Client' . $fd . ' Closed\n';
});

// 启动服务器
$serv->start();




