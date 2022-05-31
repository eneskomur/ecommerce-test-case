<?php 
/*
_|          _|_|      _|_|_|    _|_|    _|_|_|    
_|        _|    _|  _|        _|    _|  _|    _|  
_|        _|    _|  _|  _|_|  _|_|_|_|  _|_|_|    
_|        _|    _|  _|    _|  _|    _|  _|    _|  
_|_|_|_|    _|_|      _|_|_|  _|    _|  _|    _|  

Bir cisim yaklaşıyor efendim.
*/

namespace app;

use Flight;

class Logar {
    
    public function newLog($user_id,$type,$data)
    {
        global $db;

        $request = Flight::request();
        $response = [];

        $db->insert("logs", [
            "user_id" => $user_id,
            "ip" => $request->ip,
            "type" => $type,
            "data" => $data 
        ]);

        if(null !== $db->id()) {
            $response["id"] = $db->id();
            $response["success"] = "true";
        }

        return (object)$response;
    }

}