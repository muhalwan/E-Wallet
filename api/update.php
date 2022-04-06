<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') :
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Metode Permintaan Tidak Valid. Metode HTTP harus PUT',
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
            $user->nama = $data->nama;
            $user->email = $data->email;
            $user->kata_sandi = $data->kata_sandi;
            $user->id_user = $data->id_user;

            // update user will be here
            // update the user record
            if($user->update()){
                //jwt harus masih sama 

                // set response code
                http_response_code(200);
            
                echo json_encode(
                    array(
                        "message" => "User dengan ID = $user->id_user telah terupdate.",
                        "jwt" => $jwt
                    )
                );
            }
            // message if unable to update user
            else{
                // set response code
                http_response_code(401);
            
                // show error message
                echo json_encode(array("message" => "Tidak dapat mengupdate user."));
            }
        }
        elseif($decoded->data->role == "2"){
            $user->nama = $data->nama;
            $user->email = $data->email;
            $user->kata_sandi = $data->kata_sandi;
            $user->id_user = $decoded->data->id_user;
            
            // update user will be here
            // update the user record
            if($user->update()){
                // regenerate jwt will be here
                $token = array(
                    "iat" => $issued_at,
                    "exp" => $expiration_time,
                    "iss" => $issuer,
                    "data" => array(
                        "id_user" => $user->id_user,
                        "nama" => $decoded->data->nama,
                        "email" => $user->email,
                        "role" => $decoded->data->role
                    )
                );
                // generate jwt
                $jwt = JWT::encode($token, $key);

                // set response code
                http_response_code(200);
            
                echo json_encode(
                    array(
                        "message" => "User dengan ID = $user->id_user telah terupdate.",
                        "jwt" => $jwt
                    )
                );
            }
            // message if unable to update user
            else{
                // set response code
                http_response_code(401);
            
                // show error message
                echo json_encode(array("message" => "Tidak dapat mengupdate user."));
            }
        }
        else{
            // set response code
            http_response_code(400);
            
            // display message: unable to create user
            echo json_encode(array("message" => "Akses hanya untuk admin & user"));
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
else{
    // set response code
    http_response_code(401);
 
    // tell the user access denied
    echo json_encode(array("message" => "Akses ditolak."));
}
?>