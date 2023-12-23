<?php

namespace MVC\Service;

use MVC\Container;
use ORM\SQLOperations;
use PDO;

class ContainerService
{

    public static function containerInit(Container $container): Container
    {
        $container->set('ORM\SQLOperations', function () {
            $parsed = explode('://', $_ENV['DATABASE_URL'])[1];
            $pattern = '`(?<user>[A-z-0-9-]+):(?<password>.+)@(?<host>([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}):?([0-9]{1,5}))/(?<db_name>[A-z-0-9-]+)`';
            preg_match_all($pattern, $parsed, $matches);
            $pdo = new PDO(
                "mysql:host={$matches['host'][0]};dbname={$matches['db_name'][0]}",
                $matches['user'][0],
                $matches['password'][0]
            );
            return new SQLOperations($pdo);
        });

        return $container;
    }

}