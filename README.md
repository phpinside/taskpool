1、安装好docker和docker-compose，进入taskpool的目录下面，然后执行如下命令：
    
  $ docker-compose up

2、首先，假定我们docker服务器的ip为 192.168.100.99

3、首先远程登录MySQL，并初始化数据库

$ mysql -h192.168.100.99 -uroot -p my123456

$ create database taskpool default charset utf8;

$ use taskpool;

$ set names utf8;

$ \. docs\taskpool.sql

4、登录phpldapadmin，并初始化用户
https://192.168.100.99:6443/
登录用户名为：cn=admin,dc=intra,dc=denggao,dc=org
密码为：ldap123456

先添加一个【Generic: Organisational Unit】
名称为：tech  对应 config\main.php中的ldap设置

然后在tech下添加子节点，每一个子节点都是一个taskpool的用户。
【Courier Mail: Account】
注意：Common Name 就是真实的登录用户名哦！


5、需要在宿主机上加一个定时任务：
*/5 * * * *  docker-compose exec   task  php /var/www/default/public_html/yiic timer start

每5分钟执行一次分发任务的动作。
