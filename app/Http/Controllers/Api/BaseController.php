<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller as Controller;

class BaseController extends Controller
{
    public function sendResponse($result, $message, $code = 200)
    {
        return response()->json([
            'status'  => 'Sukses',
            'data'    => $result,
            'message' => $message,
        ], $code);
    }
  
    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'status'  => 'Gagal',
            'message' => $error,
        ];
  
        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }
  
        return response()->json($response, $code);
    }
}
