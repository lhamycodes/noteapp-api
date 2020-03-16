<?php
namespace App\Transformers;

class Json
{
    public static function response($status = null, $data = null, $message = null)
    {
        return [
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ];
    }
}
