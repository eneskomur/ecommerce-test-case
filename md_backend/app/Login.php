<?php 
namespace app;

use Flight;
use app\Sessionfaction as Session;
use app\Logar as Log;

class Login {
    
    public function loginAttempt($username, $password)
    {
        global $db;
        $response = [];

        $response["data"] = $db->get('users', ['id','username','email'], ['username' => $username,'password' => $password]); //md5($password);

        if(isset($response["data"]) && count($response["data"])) {
            $response["success"] = "true";
            $response["data"]["token"] = (new Session)->newSession($response["data"]["id"])->token;

            (new Log)->newLog($response["data"]["id"],'login',json_encode($response));
        }
        else{
            $response["error_code"] = '401';
            $response["error_message"] = 'Unauthorized';
            $response["error_description"] = 'Your login details are incorrect.';
            $response["success"] = "false";

            (new Log)->newLog('','login',json_encode($response));
        }

        

        return (object)$response;
    }

    public function logout($token)
    {
        global $db;
        $response = [];

        $this->user_info = (new Session)->checkSession($token);
        if($this->user_info->success == "false") {
            die(Flight::json($this->user_info));
        }

        $data = $db->delete('sessions', ['user_id' => $this->user_info->id,'token' => $token]);

        if($data) {
            $response["success"] = "true";
        }
        else{
            $response["error_code"] = '401';
            $response["error_message"] = 'Unauthorized';
            $response["success"] = "false";
        }
        (new Log)->newLog($this->user_info->id,'logout',json_encode($response));

        return (object)$response;
    }

}