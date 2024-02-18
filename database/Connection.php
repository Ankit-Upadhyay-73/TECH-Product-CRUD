<?php
    class Connection{
        public static function make($db_config){

            $host = $db_config['host'];
            $user = $db_config['user'];
            $password = $db_config['password'];
            $db =  $db_config['db']; 

            $options = [
                PDO::ATTR_EMULATE_PREPARES   => false, // turn off emulation mode for "real" prepared statements
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, //turn on errors in the form of exceptions
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, //make the default fetch be an associative array
              ];            

            try{
                $pdo = new PDO("mysql:host=$host;dbname=$db",$user,$password,$options);
                return $pdo;
            
            }
            catch(Exception $e){

                die("Failed to connect, $e");

            }


        }

    }