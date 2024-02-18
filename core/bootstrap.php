<?php

    session_start();

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

    require 'RateLimiter.php';

    $rateLimiter = new RateLimiter($_SERVER["REMOTE_ADDR"]);

    $limit = 1;				//	number of connections to limit user to per $minutes
    $minutes = 1;				//	number of $minutes to check for.
    $seconds = floor($minutes * 60);	//	retry after $minutes in seconds.
    
    try {
        $rateLimiter->limitRequestsInMinutes($limit, $minutes);
    } catch (RateExceededException $e) {
        header("HTTP/1.1 429 Too Many Requests");
        header(sprintf("Retry-After: %d", $seconds));
        $data = 'Rate Limit Exceeded ';
        die (json_encode($data));
    }

    App::bind('database',
        new QueryBuilder(
            Connection::make(App::get('config')['database'])
        )
    );

    echo Router::load("routes.php")
            ->direct(Request::uri(),Request::method());

