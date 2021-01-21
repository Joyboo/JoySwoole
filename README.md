
#### 基于 [EasySwoole](https://github.com/easy-swoole/easyswoole) 

#### 文档参考 [Doc](https://github.com/easy-swoole/doc)

- php >= 7.2
- swoole >= 4.4.13


**克隆项目**
> git clone https://github.com/Joyboo/JoybooSwoole.git

**安装Composer依赖**
> composer install
 
**启动**
> php easyswoole server start

**进度**
- [x] Worker
- [x] Crontab
- [ ] Woker,Crontab自动注册
- [ ] Crontab支持从数据库配置
- [x] Timer
- [x] HttpServer
- [ ] 模型封装
- [ ] 修改Logger处理器
- [ ] 修改Trigger处理器
- [ ] 修改Error处理器
- [ ] 修改Shutdown处理器
- [ ] 修改HttpException全局处理器
- [ ] 设置Http全局OnRequest及AfterRequest事件
- [ ] 注册子服务
- [ ] SwooleTable/Atomic共享内存
- [ ] 注册mysql,redis连接池
- [ ] 注册回调事件
