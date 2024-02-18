<?php
    use Firebase\JWT\BeforeValidException;
    use Firebase\JWT\ExpiredException;
    use Firebase\JWT\JWT;
    use Firebase\JWT\SignatureInvalidException;
    function dd($data){
        echo "<pre>";
            var_dump($data);
        echo "</pre>";
        die();
    }
    function validate($_DATA, $validations, $errors){
        $errors = [];

        foreach($validations as $field=>$rules){

            foreach($rules as $key=>$rule){

                switch($rule){

                    case 'unique':
                        $result = App::get('database')
                            ->selectWhere('users',
                                [
                                    'first'=>'users.email',
                                    'condition' => '=',
                                    'second' => $_DATA[$field] ?? null
                                ],'asc',false,
                                [ "users.email" , "users.password"]
                            );
                        
                        if(!empty($result))
                            $errors[$field][$rule] = "email already in use! Continue login";
                        break;

                    case 'email':   
                        if( !preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $_DATA[$field] ?? null)) {
                            $errors[$field][$rule] = "Invalid Email";   
                        }
                        break;

                    case 'nonumber':
                        if( preg_match("/\d/", $_DATA[$field] ?? null)) {
                            $errors[$field][$rule] = "Invalid Input";   
                        }
                        break;
    
                    default:
                        break;

                }

                switch ($key){
                    case 'min':
                        if( strlen($_DATA[$field] ?? null)  < $rule ){
                            $errors[$field][$key] = "Minimum $rule characters required" ;
                        }
                        break;

                    case 'max':
                        if( strlen($_DATA[$field] ?? null) > $rule ){
                            $errors[$field][$key] = "Maximum $rule characters allowed" ;
                        } 
                        break;
                        
                    default:
                        break;
                }

            }

        }

        return $errors;

    }
    function generateJwtToken($user_id, $secret_key){
        $issued_at = time();
        $expiration_time = $issued_at + (60*60);

        $payload = array(
            'user_id' => $user_id,
            'exp'=> $expiration_time
        );
        return createToken($payload);
    }
    
    function createToken($payload)
    {
        $base64UrlHeader = base64UrlEncode(json_encode(["alg" => "HS256", "typ" => "JWT"]));
        $base64UrlPayload = base64UrlEncode(json_encode($payload));
        $base64UrlSignature = hash_hmac('sha256', $base64UrlHeader . '.' . $base64UrlPayload, App::get('config')['SECRET_KEY'], true);
        $base64UrlSignature = base64UrlEncode($base64UrlSignature);
        return $base64UrlHeader . '.' . $base64UrlPayload . '.' . $base64UrlSignature;
    }
    
    function base64UrlEncode($data)
    {
        $base64 = base64_encode($data);
        $base64Url = strtr($base64, '+/', '-_');
        return rtrim($base64Url, '=');
    }
    
    function base64UrlDecode($data)
    {
        $base64 = strtr($data, '-_', '+/');
        $base64Padded = str_pad($base64, strlen($base64) % 4, '=', STR_PAD_RIGHT);
        return base64_decode($base64Padded);
    }

    function validateToken($token)
    {
        // Implementation for validating JWT
        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = explode('.', $token);
    
        $signature = base64UrlDecode($base64UrlSignature);
        $expectedSignature = hash_hmac('sha256', $base64UrlHeader . '.' . $base64UrlPayload, App::get('config')['SECRET_KEY'], true);
    
        return hash_equals($signature, $expectedSignature);
    }
    
    function decodeToken($token)
    {
        // Implementation for decoding JWT
        if(!$token)
            return null;
        
        list(, $base64UrlPayload, ) = explode('.', $token);
        $payload = base64UrlDecode($base64UrlPayload);
        return json_decode($payload, true);
    }    