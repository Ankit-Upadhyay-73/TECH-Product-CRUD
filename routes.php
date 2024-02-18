
<?php

    $router->get("","HomeController@index");
    $router->post("login","AuthController@attempt");
    $router->post("register","RegisterController@register");

    $router->post("product","ProductController@store");
    $router->get("products","ProductController@list");
    $router->get("product/show","ProductController@show");  
    $router->put("product/edit","ProductController@update");
    $router->delete("product","ProductController@remove");