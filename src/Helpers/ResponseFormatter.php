<?php

namespace App\Helpers;

class ResponseFormatter
{
    public static function format($message, $error = true, $status = null)
    {
        if ($error) {
            $content = [
                'error' => $message,
            ];
        }else {
            $content = [
                'message' => $message,
            ];
        }

        return  \response($content, is_null($status) ? ($error ? 400 : 200) : $status,["Content-type" => 'application/json']);
    }
}
