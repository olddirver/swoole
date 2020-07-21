###### TCP服务器

```
//创建Server对象，监听 127.0.0.1:9501端口
$serv = new Swoole\Server("127.0.0.1", 9501); 
```
> 创建一个异步IO的server对象
```
Swoole\Server(string $host = '0.0.0.0', int $port = 0, int $mode = SWOOLE_PROCESS, int $sockType = SWOOLE_SOCK_TCP): \Swoole\Server
```
- string $host 
监听的ip地址

- int $port
监听的端口

如果$socketType值为UnixSocket Stream/Dgram，此参数将被忽略
监听小于1024端口需要root权限
如果此时端口被占用server ->start时会失效

- int $mode
运行的模式
1. SWOOLE_PROCESS 多进程模式(默认)
2. SWOOLE_BASE 基本模式
3. 线程模式 

- int $sock_type
server的类型
1. SWOOLE_TCP/SWOOLE_SOCK_TCP tcp ipv4 socket
2. SWOOLE_TCP6/SWOOLE_SOCKE_TCP6 tcp ipv6 socket
3. SWOOLE_UDP/SWOOLE_SOCK_UDP udp ipv4 socket
4. SWOOLE_UDP6/SWOOLE_SOCK_UDP6 udp ipv6 socket
5. SWOOLE_UNIX_DGRAM unix socket dgram // 基于UDP协议 unix套接字接口
6. SWOOLE_UNIX_STREAM unix socket stream // 基于TCP协议 unix套接字接口

使用$sock_type | SWOOLE_SSL 可以启用SSL隧道加密，启用SSL后必须配置ssl_key_file和ssl_cert_file

> 什么是IPC？
同一台主机上两个进程间通信(简称IPC)

- Unix Socket

全名 UNIX Domain Socket 简称UDS，使用套接字的API(socket，bind，listen，connet，read，weite，close等)，和TCP/IP不同的是不需要指定ip和port，而是通过一个文件名来表示(例如：FPM和Nginx之间的/tem/php-fcgi.sock)，UDP是linux内核实现的全内存通信，无任何IO消耗。在1进程write，1进程read，每次读写1024字节数据的测试中，100万次通信仅需1.02秒，功能非常强大，swoole默认使用这种IPC方式。
SOCK_STREAM和SOCK_DGRAM
1. Swoole下面使用UDS通信方式有两种，SOCK_STREAM和SOCK_DGRAM，可以理解为TCP和UDP的区别，当使用SOCK_STREAM类型的时候需要考虑TCP粘包问题
2. 当使用SOCK_DGRAM类型的时候不需要考虑粘包问题，每个send()的数据都是有边界的，发送多大的数据接收的时候就收到多大的数据，没有传输过程中丢包、乱序问题。send写入和recv读取的顺序完全一致。send返回成功后一定是可以recv到。
在IPC传输的数据比较小时非常适合用SOCK_DGRAM这种方式，由于IP包每个最大有64k的限制，所以SOCK_DGRAM进行IPC的时候每次发送数据不能大于64k，同时注意收包速度太慢操作系统缓冲区满了会丢弃包，因为UDP是允许丢包的，可以适当增大缓冲区。

- sysvmsg

即Linux提供的消息队列，这种IPC方式通过一个文件名来作为key进行通信
1. 防止丢数据，如果整个服务都挂掉，再次启动队列中的消息也在，可以继续消费，但同样会有脏数据问题。
2. 可以外部投递数据，比如Swoole下的Worker进程通过消息队列给Task进行投递任务，第三方的进程也可以投递任务到队列里面让Task消费，甚至可以在命令行手动添加消息到队列。