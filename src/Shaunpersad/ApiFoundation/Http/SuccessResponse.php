<?php
namespace Shaunpersad\ApiFoundation\Http;

use App;
use Response;

class SuccessResponse {

    public static function make($object, $status = 200, array $headers = array(), $options = 0 ) {

        $response = App::make(
            'api_response_array',
            array(
                'content' => $object
            )
        );

        return Response::json($response, $status, $headers, $options);
    }
} 