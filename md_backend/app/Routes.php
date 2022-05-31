<?php 
namespace app;

use Flight;
use app\Login as Login;
use app\Products as Products;

class Routes {
    
    public function __construct()
    {
        Flight::route('/', function(){
            echo Flight::json(['server_status' => 'vivus sum','success' => true]);
        });
        
        Flight::route('POST /login', function(){
            $request = Flight::request();
            $this->requiredParameters(['username','password'],$request->data);

            $validate = (new Login)->loginAttempt($request->data->username, $request->data->password);
            echo Flight::json($validate);

            /* if($request->query->username == 'enes') {
                echo Flight::json(['server_status' => 'sen doğru','success' => true]);
            }
            else {
                echo Flight::json(['server_status' => 'sen çok yanlış bi insansın','success' => true]);
            } */
            /* echo Flight::json($request); */
        });

        Flight::route('GET /logout', function(){
            $request = Flight::request();

            $response = (new Login)->logout($request->query->token);
            echo Flight::json($response);
        });

        Flight::route('GET /products', function(){
            $request = Flight::request();

            if(isset($request->query->limit)) $limit = $request->query->limit;
            else if(isset($request->query->length)) $limit = $request->query->length;
            else $limit = 10;

            // Will try to use the "page" value first. Otherwise second option is "offset".
            if(isset($request->query->page)) $offset = ($request->query->page - 1) * $limit;
            else if(isset($request->query->offset)) $offset = $request->query->offset;
            else if(isset($request->query->start)) $offset = $request->query->start;
            else $offset = 0;

            // Object <-> Array conflict fix
            if(isset($request->query->search)) {
                $search = $request->query->search;
                if(!isset($search["value"])) $search["value"] = '';
            }
            else $search["value"] = '';
            
            $products = (new Products($request->query->token))->getAllProducts($offset, $limit, $search["value"]);
            echo Flight::json($products);
        });

        Flight::route('GET /product', function(){
            $request = Flight::request(); echo "<pre>"; 
            $this->requiredParameters(['product_id'],$request->query);

            $product = (new Products($request->query->token))->getOneProduct($request->query->product_id);
            echo Flight::json($product);
        });

        Flight::route('POST /product/edit', function(){
            $request = Flight::request();
            $this->requiredParameters(['product_id'],$request->data);

            $check = (new Products($request->query->token))->getOneProduct($request->data->product_id);

            if(!$check->success){
                die(Flight::json(['error_code' => '403','error_message' => 'Forbidden','success' => false]));
            }
            
            $product = (new Products($request->query->token))->editProduct($request->data->product_id,$request->data);
            echo Flight::json($product);
        });

        Flight::route('POST /product/delete', function(){
            $request = Flight::request();
            $this->requiredParameters(['product_id'],$request->data);

            $check = (new Products($request->query->token))->getOneProduct($request->data->product_id);

            if(!$check->success){
                die(Flight::json(['error_code' => '403','error_message' => 'Forbidden','success' => false]));
            }
            
            $product = (new Products($request->query->token))->deleteProduct($request->data->product_id);
            echo Flight::json($product);
        });

        Flight::route('POST /product/add', function(){
            $request = Flight::request();
            $this->requiredParameters(['name','price'],$request->data);
            
            $product = (new Products($request->query->token))->addProduct($request->data);
            echo Flight::json($product);
        });
        
        Flight::map('notFound', function(){
            echo Flight::json(['error_code' => '404','error_message' => 'Not Found','success' => false,'request' => Flight::request()]);
        });
        
        // catch everything
        /* Flight::route('/*', array($pages, 'redirect')); */
    }

    public function requiredParameters($parameters, $haystack) {
        foreach($parameters as $parameter) {
            if(!isset($haystack->$parameter)) {
                die(Flight::json(['error_code' => '400','error_message' => 'Bad Request','error_description' => "{$parameter} parameter is required.",'success' => false]));
            }
        }
    }

}