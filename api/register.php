<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') :
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Metode Permintaan Tidak Valid. Metode HTTP harus POST',
    ]);
    exit;
endif;

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
 
// set product property values
$user->nama = $data->nama;
$user->email = $data->email;
$user->kata_sandi = $data->kata_sandi;
$user->role = 2;

// create the user
if(
    !empty($user->nama) &&
    !empty($user->email) &&
    !empty($user->kata_sandi) &&
    $user->create()
){
    // set response code
    http_response_code(200);
 
    // display message: user was created
    echo json_encode(array("message" => "User telah dibuat."));
}
// message if unable to create user
else{
    // set response code
    http_response_code(400);
 
    // display message: unable to create user
    echo json_encode(array("message" => "Tidak dapat membuat user."));
}
?>