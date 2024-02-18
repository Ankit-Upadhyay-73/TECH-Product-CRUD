<?php
    class AuthController
    {

        public function create()
        {
            return require 'views/login.php';
        }

        public function attempt(){
        
            $validations = [
                "email"     => ['email'],
                "password"  => ['min'=>8]
            ];

            $errors = [];

            $jsonData = file_get_contents('php://input');
            // Decode the JSON data into a PHP associative array
            $data = json_decode($jsonData, true);            

            $errors = validate($data,$validations,$errors);

            header("HTTP/1.1 200 OK");
            header('Content-Type: application/json; charset=utf-8');
            if(count($errors) > 0){
                dd($errors);
                header("HTTP/1.1 422 unprocessable entity");
                return json_encode(array("message"=>"Invalid field value"));
            }

            $user_name = $data['email'];

            $columns =  [
                            "users.id",
                            "users.email",
                            "users.password",
                            "users.firstname",
                            "users.lastname"
                        ];
            
            $v_user = App::get('database')
                ->selectWhere('users',[
                                "first" => "users.email",
                                "condition" => "=",
                                "second" => "$user_name"
                            ],'asc',false,$columns
                );
            
            $v_password = password_verify($data['password'],!empty($v_user['password']) ? $v_user['password'] : null);
            
            if($v_user && $v_password){
                $errors = [];

                unset($v_user['password']);

                $jwtToken = generateJwtToken($v_user['id'], APP::get('config')['SECRET_KEY']);
                $responseData = array('token' => $jwtToken);

                return json_encode($responseData);
            }else{
                if(empty($v_user))
                    $errors["email"][] = "Account doesn't exists, Register Yourself";
                else 
                    $errors["email"][] = "email or password incorrect";

                header("HTTP/1.1 422 unprocessable entity");
                return json_encode(array('errors'=>$errors));
            }         

        }

        public function logout(){

            unset($_SESSION['user']);

            header('location: /');

        }

    }