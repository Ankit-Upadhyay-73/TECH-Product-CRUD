<?php
    require 'App.php';
    require 'public/helper.php';
    App::bind('config',require 'config.php');
    require 'database/QueryBuilder.php';
    require 'database/Connection.php';
    require 'Router.php';
    require 'Request.php';
    require 'controllers/RegisterController.php';
    require 'controllers/AuthController.php';
    require 'controllers/ProductController.php';
    require 'controllers/HomeController.php';
    App::bind('database',
        new QueryBuilder(
            Connection::make(App::get('config')['database'])
        )
    );
    echo Router::load("routes.php")
            ->direct(Request::uri(),Request::method());

