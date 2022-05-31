<?php 
/*
Push me and then just touch me
Till I can get my
Sessionfaction...
Enes Kömür 
For test case
*/

namespace app;

use Flight;

class Sessionfaction {
    
    public function newSession($user_id)
    {
        global $db;

        $response = [];

        $token = $this->generateRandomString();

        $db->insert("sessions", [
            "user_id" => $user_id,
            "token" => $token 
        ]);

        if(null !== $db->id()) {
            $response["token"] = $token;
            $response["success"] = "true";
        }
        else{
            $response["error_code"] = '401';
            $response["error_message"] = 'Unauthorized';
            $response["error_description"] = 'Account not found';
            $response["success"] = "false";
        }

        return (object)$response;
    }

    public function checkSession($token)
    {
        global $db;

        $response = [];

        $user_check = $db->select('sessions', ['user_id'], ['token' => $token]);

        if(count($user_check)) {
            $response = $db->select('users', ['id','username','email'], ['id' => $user_check[0]["user_id"]])[0];
            $response["success"] = "true";
        }
        else{
            $response["error_code"] = '401';
            $response["error_message"] = 'Unauthorized';
            $response["error_description"] = 'Invalid token';
            $response["success"] = "false";
        }

        return (object)$response;
    }

    function generateRandomString($length = 16) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

}