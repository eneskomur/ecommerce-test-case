<?php 
namespace app;

use Flight;
use app\Sessionfaction as Session;
use app\Logar as Log;
use Exception;

class Products {

    public function __construct($token)
    {
        $this->user_info = (new Session)->checkSession($token);

        if($this->user_info->success == "false") {
            die(Flight::json($this->user_info));
        }
    }
    
    public function getAllProducts($offset = 0, $limit = 10, $search = '')
    {
        global $db;
        $response = [];
        $conditions = ['user_id' => $this->user_info->id, 'status' => '1', "LIMIT" => [$offset, $limit], "ORDER" => ["update_time" => "DESC"]];
        
        if(strlen(trim($search))>0) {
            $conditions["name[~]"] =  $search;
            $response["recordsFiltered"] = $db->count('products', ['user_id' => $this->user_info->id, 'status' => '1', "name[~]" => $search]);
        }

        $response["data_raw"] = $db->select('products', '*', $conditions);
        $response["recordsTotal"] = $db->count('products', ['user_id' => $this->user_info->id, 'status' => '1']);
        $response["recordsFiltered"] = isset($response["recordsFiltered"]) ? $response["recordsFiltered"] : $response["recordsTotal"];
        $response["total_pages"] = ceil($response["recordsTotal"] / $limit);
        $response["current_page"] = floor($offset/$limit) + 1;
        
        $response["data"] = [];

        foreach($response["data_raw"] as $data_line) {
            $line = [];  

            $line[] = $data_line["name"];
            $line[] = $data_line["description"];
            $line[] = $data_line["price"];
            $line[] = $data_line["discount"] == "1" ? "Apply" : "Don't apply";
            $line[] = $data_line["sale_status"] == "1" ? "On sale" : "Sale stopped";
            $line[] = $data_line["update_time"];

            $response["data"][] = $line;
        }

        if(count((array)$response["data"])) {
            $response["success"] = "true";
        }
        else{
            $response["error_code"] = '200';
            $response["error_message"] = 'There is no product.';
            $response["success"] = "true";
        }
        (new Log)->newLog($this->user_info->id,'list products',json_encode($response));

        return $response;
    }

    public function getOneProduct($product_id)
    {
        global $db;
        $response = [];

        $response["data"] = (object)$db->get('products', '*', ['id' => $product_id, 'user_id' => $this->user_info->id, 'status' => '1']);

        if(count((array)$response["data"])) {
            $response["success"] = "true";
        }
        else{
            $response["error_code"] = '404';
            $response["error_message"] = 'Not Found';
            $response["success"] = "false";
        }
        (new Log)->newLog($this->user_info->id,'get product',json_encode($response));
        
        return (object)$response;
    }

    public function editProduct($product_id,$query)
    {
        global $db;
        $response = [];
        $changed_datas = [];
        $filters = ['name', 'description', 'price', 'discount', 'sale_status', 'status'];

        foreach($filters as $filter) {
            if(isset($query->$filter)) {
                $changed_datas[$filter] = (string)$query->$filter;
            }
        }

        $data = (object)$db->update('products', $changed_datas, ['AND' => ['id' => $product_id, 'user_id' => $this->user_info->id] ]);

        if(count((array)$data)) {
            $response["changed_datas"] = (object)$changed_datas;
            $response["success"] = "true";
        }
        else{
            $response["error_code"] = '418';
            $response["error_message"] = 'I\'m a teapot';
            $response["success"] = "false";
        }
        (new Log)->newLog($this->user_info->id,'edit product',json_encode($response));
        
        return (object)$response;
    }

    public function deleteProduct($product_id)
    {
        global $db;
        $response = [];

        $data = (object)$db->update('products', ['status' => '0'], ['AND' => ['id' => $product_id, 'user_id' => $this->user_info->id] ]);

        if(count((array)$data)) {
            $response["success"] = "true";
            $response["product_id"] = $product_id;
        }
        else{
            $response["error_code"] = '418';
            $response["error_message"] = 'I\'m a teapot';
            $response["success"] = "false";
        }
        (new Log)->newLog($this->user_info->id,'delete product',json_encode($response));
        
        return (object)$response;
    }

    public function addProduct($query)
    {
        global $db;
        $response = [];
        $added_datas = ['user_id' => $this->user_info->id];
        $filters = ['name', 'description', 'price', 'discount', 'sale_status', 'status'];

        foreach($filters as $filter) {
            if(isset($query->$filter)) {
                $added_datas[$filter] = (string)$query->$filter;
            }
        }

        $data = (object)$db->insert('products', $added_datas);

        if(count((array)$data)) {
            $response["success"] = "true";
            $response["message"] = "Your product added successfully.";
            $response["added_datas"] = (object)$added_datas;
        }
        else{
            $response["error_code"] = '418';
            $response["error_message"] = 'I\'m a teapot';
            $response["success"] = "false";
        }
        (new Log)->newLog($this->user_info->id,'add product',json_encode($response));
        
        return (object)$response;
    }

}