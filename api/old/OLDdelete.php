<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') :
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Metode Permintaan Tidak Valid. Metode HTTP harus DELETE',
    ]);
    exit;
endif;

// required to encode json web token
include_once 'config/core.php';
include_once 'libs/php-jwt-main/src/BeforeValidException.php';
include_once 'libs/php-jwt-main/src/ExpiredException.php';
include_once 'libs/php-jwt-main/src/SignatureInvalidException.php';
include_once 'libs/php-jwt-main/src/JWT.php';
use \Firebase\JWT\JWT;

// database connection will be here
// files needed to connect to database
include_once 'config/database.php';
include_once 'object/user.php';
 
// get database connection
$database = new Database();
$db = $database->getConnection();
 
// instantiate product object
$user = new User($db);

// get posted data
$data = json_decode(file_get_contents("php://input"));
 
// get jwt
$jwt=isset($data->jwt) ? $data->jwt : "";
 
// decode jwt here
// if jwt is not empty
if($jwt){
    // if decode succeed, show user details
    try {
        // decode jwt
        $decoded = JWT::decode($jwt, $key, array('HS256'));
        
        if($decoded->data->role == "1"){
            // set user property values here
            // set user property values
            // if you want to delete id_user as you wish
            $user->id_user = $data->id_user;
            // if you want to delete id_user from jwt
            //$user->id_user = $decoded->data->id_user;
            
            // delete the user record
            if($user->delete()){
                // set response code
                http_response_code(200);
            
                echo json_encode(
                    array(
                        "message" => "User dengan ID = $user->id_user telah terhapus."
                    )
                );
            }
            // message if unable to delete user
            else{
                // set response code
                http_response_code(401);
            
                // show error message
                echo json_encode(array("message" => "Tidak dapat menghapus user."));
            }
        }
        else{
            // set response code
            http_response_code(400);
            
            // display message: unable to create user
            echo json_encode(array("message" => "Akses hanya untuk admin"));
        }
    }
    // catch failed decoding will be here
    // if decode fails, it means jwt is invalid
    catch (Exception $e){
    
        // set response code
        http_response_code(401);
    
        // show error message
        echo json_encode(array(
            "message" => "Akses ditolak.",
            "error" => $e->getMessage()
        ));
    }
}
// error message if jwt is empty will be here
// show error message if jwt is empty
else{
    // set response code
    http_response_code(401);
 
    // tell the user access denied
    echo json_encode(array("message" => "Akses ditolak (Tidak terdapat token)."));
}
?>