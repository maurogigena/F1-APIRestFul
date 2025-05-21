<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponses {

    protected string $namespace = 'App\\Policies\\Api';

    protected function ok($message, $data = []): JsonResponse 
    {
        return $this->success($message, $data, 200);
    }
    
    protected function success($message, $data = [], $statusCode = 200): JsonResponse 
    {
        return response()->json([
            'data' => $data,
            'message' => $message,
            'status' => $statusCode
        ], $statusCode);
    }

    // app.php / ->withExceptions() <->
    // that function will provide a customizer error in json format
    
    // GENERAL FUNCTION TO RETURN CUSTOMIZED ERRORS
    protected function error($errors = [], $statusCode = 500): JsonResponse 
    {
        if (is_string($errors)) {
            return response()->json([
                'message' => $errors,
                'status' => $statusCode
            ], $statusCode);
        }

        return response()->json([
            'errors' => $errors
        ], $statusCode);
    }

    // SPECIFIC FUNCTION TO HANDLING AUTHORIZATION ERRORS (401 - UNAUTHORIZED)
    protected function notAuthorized($message, $statusCode = 401): JsonResponse 
    {
        return $this->error([
            'status' => $statusCode,
            'message' => $message,
            'source' => ''
        ], $statusCode);
    }

    /**
     * Return a 204 No Content response.
     *
     * @return \Illuminate\Http\Response
     */
    protected function noContent()
    {
        return response()->noContent();
    }
}