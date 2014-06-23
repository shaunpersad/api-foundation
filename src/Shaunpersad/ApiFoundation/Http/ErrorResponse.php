<?php
namespace Shaunpersad\ApiFoundation\Http;

use App;
use Response;

class ErrorResponse {

    public static function make( $message = '', $status = 200, array $headers = array() ) {

        $response = App::make(
            'api_response_array',
            array(
                'content' => null,
                'error' => true,
                'error_description' => $message
            )
        );
        return Response::json($response, $status);
    }
} 