<?php
function response($response, $code = 200, $success = true){
    return json_encode(array("code" => $code, "response" => $response, "success" => $success));
}