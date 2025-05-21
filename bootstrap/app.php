<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: [// <-- we can use an array here
            __DIR__.'/../routes/api.php',
            __DIR__.'/../routes/api_v1.php',
        ],
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })

    // HANDLING ERRORS
    ->withExceptions(function (Exceptions $exceptions) {

        // HANDILNG VALIDATIONEXCEPTION ERRORS, LI KE ...  
        $exceptions->render(function (ValidationException $e) {

            $className = get_class($e);

            // if it's an explicit validationexception
            if ($className == ValidationException::class) {
                $errors = [];

                foreach ($e->errors() as $key => $messages) {
                    foreach ($messages as $message) {
                        $errors[] = [
                            'type' => 'ValidationException',
                            'status' => 422,
                            'message' => $message,
                            'source' => $key
                        ];
                    }
                }

                // return the errors in json format with proper status code
                return response()->json(['errors' => $errors], 422);
            }

            // if it's other exception that inherits validationexception 
            $index = strrpos($className, '\\');

            $errors = [];
            // run through all the errors and build a custom response
            foreach ($e->errors() as $key => $messages) {
                foreach ($messages as $message) {
                    $errors[] = [
                        'type' => substr($className, $index + 1), // kind of exception
                        'status' => 0,
                        'message' => $message,
                        'source' => 'Line: ' . $e->getLine() . ': ' . $e->getFile() // error origin data
                    ];
                }
            }
            
            // return the errors in json format
            return response()->json([
                'errors' => $errors,
            ]);
        });
    
        // ERROR 404
        $exceptions->render(function (NotFoundHttpException $e) {
            $previous = $e->getPrevious();
        
            // if the error caused by a not found model (ModelNotFoundException) returns ...
            if ($previous instanceof ModelNotFoundException) {
                return response()->json([
                    'errors' => [
                        [
                            'status' => 404,
                            'message' => 'The resource cannot be found.',
                            'source' => $previous->getModel() // Ej: App\\Models\\Ticket
                        ]
                    ]
                ], 404);
            }
        
            // if the error was by another kind of HttP error (NotFoundHttpException) returns ...
            return response()->json([
                'errors' => [
                    [
                        'status' => 0,
                        'message' => 'Page not found.',
                        'source' => 'Line: ' . $e->getLine() . ': ' . $e->getFile()
                    ]
                ]
            ], 404);
        });
    
        // HERROR 401
        $exceptions->render(function (AuthenticationException $e) {
            $className = get_class($e);
            $index = strrpos($className, '\\');

            return response()->json([
                'errors' => [
                    [
                        'type' => substr($className, $index + 1), // AuthenticationException
                        'status' => 401,
                        'message' => 'Authentication required. Please login to access this resource.',
                        'source' => 'Line: ' . $e->getLine() . ': ' . $e->getFile()
                    ]
                ]
            ], 401);
        });

        // ERROR 403
        $exceptions->render(function (AuthorizationException $e) {
            $className = get_class($e);
            $index = strrpos($className, '\\');

            return response()->json([
                'errors' => [
                    [
                        'type' => substr($className, $index + 1),
                        'status' => 403,
                        'message' => $e->getMessage() ?: 'This action is unauthorized.',
                        'source' => ''
                    ]
                ]
            ], 403);
        });
    })->create(); // ENDS THE HANDLING EXCEPTION SETTINGS 