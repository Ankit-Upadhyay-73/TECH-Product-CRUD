<?php

    class App{


        protected static $properties = [];

        public static function bind($key,$value){

            App::$properties[$key] = $value;

        }

        public static function get($key){

            return App::$properties[$key];
        
        }

    }