Zf1Example-UserManagement
=========================

an example of zf1 application.
example SQL file in docs/example.sql

使用 jQuery js 框架，使用  twitter bootstrap CSS 框架。

配置文件 YAML 格式。
数据库配置:
        multidb:
            default:
                adapter: "Pdo_Mysql"
                host: "127.0.0.1"
                username: "root"
                password: "123456"
                dbname: "example"

认证和授权使用 plugin 
            plugins:
                - Application_Plugin_Auth
                - Application_Plugin_Acl

缓存默认关闭，修改 cachemanager
    metadata 作为 DbTable 的  metadata 之缓存，存放数据表结构。