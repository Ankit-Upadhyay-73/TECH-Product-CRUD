<?php
    class Router
    {

        protected $routes = [
            "GET" => [],
            "POST" => [],
            "PUT" => [],
            "DELETE" => []
        ];

        public static function load($file){

            $router = new static;

            require $file;

            return $router;

        }

        public function assign($routes){
            $this->routes  = $routes;
        }

        public function direct($uri,$method){

            $uri = parse_url($uri)['path'];
            if( array_key_exists( $uri, $this->routes[$method] )){
                header('Content-Type: application/json');
                header("HTTP/1.1 200 OK");
                return $this->callAction(...explode("@",$this->routes[$method][$uri]));
            }
            throw new Exception("Invalid Route $uri");

        }

        public function callAction($controller,$method){
            if(method_exists($controller,$method))
                return (new $controller)->$method();
            throw new Exception("method $controller->$method() not found");
        
        }

        public function get($uri,$controller){

            $this->routes['GET'][$uri] = $controller;

        }

        public function post($uri,$controller){

            $this->routes['POST'][$uri] = $controller;

        }        


        public function put($uri,$controller){

            $this->routes['PUT'][$uri] = $controller;

        }
        

        public function delete($uri,$controller){

            $this->routes['DELETE'][$uri] = $controller;

        }        

    }