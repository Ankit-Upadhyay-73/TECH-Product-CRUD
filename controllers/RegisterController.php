<?php
    class RegisterController{

        public function register(){

            /**
             * Sanatize Input
             */

            $validations = [
                "email"     => ['unique','email'],
                "fname"     => ['max'=>30,'min'=>4,'nonumber'],
                "lname"     => ['max'=>30,'min'=>4,'nonumber'],
                "password"  => ['min'=>8]
            ];

            $data = json_decode(file_get_contents('php://input'), true);            
            $errors = [];
            $errors = validate($data,$validations,$errors);

            if(count($errors) > 0){
                header("HTTP/1.1 422 unprocessable entity");
                return json_encode(array("message"=>"Invalid field value"));
            }

            // Fire to register user

            $_PAYLOAD = [];

            $_PAYLOAD['firstname'] = $data['fname'];
            $_PAYLOAD['lastname'] = $data['lname'];
            $_PAYLOAD['email'] = $data['email'];
            $_PAYLOAD['password'] = password_hash($data['password'],PASSWORD_DEFAULT);

            // unset($_DATA);

            $insertedUser = App::get('database')->insert("users",$_PAYLOAD);
            
            if($insertedUser){

                $user_id  = App::get('database')->selectWhere('users',
                        [
                            "first"=>'users.email',"condition"=>"=","second"=>$_PAYLOAD['email']
                        ],
                    'desc',false,['users.id'])['id'];                

                $errors  = [];

                unset($_PAYLOAD['password']);
                
                $_PAYLOAD['id'] = $user_id;

                $jwtToken = generateJwtToken($user_id, APP::get('config')['SECRET_KEY']);
                $responseData = array('token' => $jwtToken);
                return json_encode($responseData);
            }
            else{
                header("HTTP/1.1 422 unprocessable entity");
                return json_encode(array("message"=>"Invalid field value"));
            }
        }
    }