<?php
namespace Shaunpersad\ApiFoundation\Http;

use App;
use Response;

class SuccessResponse {

    public static function make($object) {

        $response = App::make(
            'api_response_array',
            array(
                'content' => $object
            )
        );

        return Response::json($response, 200);
    }
} 