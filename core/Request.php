<?php
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT as JWT;
use Firebase\JWT\SignatureInvalidException;
    class Request
    {
       
        public static function uri(){
       
            return trim($_SERVER["REQUEST_URI"],"/");
       
        }

        public static function method(){

            return $_SERVER["REQUEST_METHOD"];

        }

        public static function user(){
            $HEADERS = apache_request_headers();
            $jwtToken = isset($HEADERS['Authorization']) ? str_replace('Bearer ','', $HEADERS['Authorization']) : null;
            try{
                return decodeToken($jwtToken);
            }
            catch(Exception $e){
                return null;
            }
        }
    }