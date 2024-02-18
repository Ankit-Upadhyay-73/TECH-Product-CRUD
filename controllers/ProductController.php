<?php
    use LDAP\Result;
    class ProductController
    {
        public function store(){
            
            if(!Request::user()){
                header("HTTP/1.1 401 Unauthorized");
                return json_encode(array("message"=>"Unauthorized"));                
            }

            $validations = [
                "name"     => ['required'],
                "price" => ['required','min'=>1],
                "category" => ['required','min'=>1]
            ];


            $jsonData = file_get_contents('php://input');
            // Decode the JSON data into a PHP associative array
            $data = json_decode($jsonData, true);            

            $_INPUT = array_map(function($item){
                return htmlspecialchars($item);
            },$data);

            $errors = [];
            $errors = validate($_INPUT,$validations,$errors);
           
            if(count($errors) > 0){
                header("HTTP/1.1 422 Unprocessable Entity");
                return json_encode(array("message"=>"Invalid input values."));                
            }

            $product = App::get('database')->insert('products',
                        [
                            "name"      =>  $_INPUT['name'],
                            "price"  =>  $_INPUT['price'],
                            "category" => $_INPUT['category'],
                            "user_id"   =>  Request::user()['user_id']
                        ]
                );
            
            if(!$product){
                dd("Failed to create product");
            }
            return json_encode(array('message'=>'Product created successfully'));

        }

        public function show(){

            if(!Request::user()){
                header("HTTP/1.1 401 Unauthorized");
                return json_encode(array("message"=>"Unauthorized"));
            }

            $jsonData = file_get_contents('php://input');
            // Decode the JSON data into a PHP associative array
            $productId = json_decode($jsonData, true)['id'];        

            $product = App::get('database')
                        ->selectWhere('products',
                            [
                                "first" => "products.id",
                                "condition" => "=",
                                "second" => "$productId"
                            ],'desc',false,[ 'products.id', 'products.name', 'products.price', 'products.category']);            

            return json_encode(array('data'=> ($product ? $product : null ) ));
        }

        public function list(){

            if(!Request::user()){
                header("HTTP/1.1 401 Unauthorized");
                return json_encode(array("message"=>"Unauthorized"));
            }

            $user_id = Request::user()['user_id'];

            $products = App::get('database')
                        ->selectWhere('products',
                            [
                                "first" => "products.user_id",
                                "condition" => "=",
                                "second" => "$user_id"
                            ],'desc',true,['products.name','products.price','products.category']);        

            return json_encode(array('data'=>$products));
        }

        public function update(){

            if(!Request::user()){
                header("HTTP/1.1 401 Unauthorized");
                return json_encode(array("message"=>"Unauthorized"));
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $productId = $input['id'];

            $dbFetch = App::get('database')
                        ->selectWhere('products',
                            [
                                "first" => "products.id",
                                "condition" => "=",
                                "second" => "$productId"
                            ],'desc',true,['products.name','products.price','products.category'])[0];    

            $product = [
                'name' => isset($input['name']) ? $input['name'] : $dbFetch['name'] ,
                'price' => isset($input['price']) ? $input['price'] : $dbFetch['price'],
                'category' => isset($input['category']) ? $input['category'] : $dbFetch['category']
            ];

            App::get('database')
                ->update('products', $productId, $product
            );
       
            return json_encode(array('message' => 'Product updated successfully'));
        }

        public function remove(){

            if(!Request::user()){
                header("HTTP/1.1 401 Unauthorized");
                return json_encode(array("message"=>"Unauthorized"));
            }

            $productId = json_decode(file_get_contents('php://input'), true)['id'];

            $dbFetch = App::get('database')
                        ->selectWhere('products',
                            [
                                "first" => "products.id",
                                "condition" => "=",
                                "second" => "$productId"
                            ],'desc',true,['products.name','products.price','products.category']);    

            if(!(isset($dbFetch[0]))){
                header("HTTP/1.1 422 Unprocessable Entity");
                return json_encode(array('message' => 'Product not found'));                
            }

            App::get('database')
                ->delete('products', $productId);
       
            return json_encode(array('message' => 'Product removed successfully'));
        }
    }