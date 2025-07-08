# php-jwt-serv-slim4

## A Basic Member Access Token Interaction Server Backend Skeleton

This project is based on the **Slim Framework 4 Skeleton Application**.  
Please refer to the [Slim 4 official website](https://www.slimframework.com/) to understand how it works.

Use this skeleton application to quickly set up and start working on an access token authorization backend with middleware.  
It uses the latest **Slim 4** framework, with Slim PSR-7 implementation and PHP-DI as the dependency injection container.  
It also integrates the **Monolog** logger.

I’ve implemented log saving using MySQL, integrated with the Monolog logger.

All required extensions and libraries are listed in `composer.json`:

```json
"php": "^8.3",
"ext-json": "*",
"doctrine/dbal": "4.0",
"doctrine/orm": "3.0",
"monolog/monolog": "^2.8",
"php-di/php-di": "^6.4",
"predis/predis": "^3.0",
"slim/psr7": "^1.5",
"slim/slim": "^4.10",
"symfony/cache": "^7.3",
"tuupola/slim-jwt-auth": "^3.8"   <---- 可以自己改寫，抽換自己喜好的程式。因為他已經很久沒有再更新了。
```
## Notice
All things about the database are in the `/src/Model/*` directory.  
I really suggest you extend the `BaseDbModelWrapper` class to make sure all export operations are consistent.  
If you don't know how to use it, there are samples available in the same directory.