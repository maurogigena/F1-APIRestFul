<?php

namespace Tests\Unit;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Tests\TestCase;

class ApiResponseTest extends TestCase
{
    // Clase anónima que usa el trait para testearlo
    protected $trait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->trait = new class {
            use \App\Traits\ApiResponses;
            public function publicOk($message, $data = []) {
                return $this->ok($message, $data);
            }
            public function publicSuccess($message, $data = [], $status = 200) {
                return $this->success($message, $data, $status);
            }
            public function publicError($errors = [], $status = 500) {
                return $this->error($errors, $status);
            }
            public function publicNotAuthorized($message, $status = 401) {
                return $this->notAuthorized($message, $status);
            }
            public function publicNoContent() {
                return $this->noContent();
            }
        };
    }

    /** @test */
    public function it_returns_ok_response()
    {
        $response = $this->trait->publicOk('Success', ['key' => 'value']);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Success', $response->getData()->message);
        $this->assertEquals(['key' => 'value'], (array)$response->getData()->data);
    }

    /** @test */
    public function it_returns_custom_success_status()
    {
        $response = $this->trait->publicSuccess('Created', [], 201);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('Created', $response->getData()->message);
    }

    /** @test */
    public function it_returns_error_with_string_message()
    {
        $response = $this->trait->publicError('Ups! Something went wrong.', 500);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('Ups! Something went wrong.', $response->getData()->message);
    }

    /** @test */
    public function it_returns_error_with_array()
    {
        $errors = ['email' => 'This field is required'];
        $response = $this->trait->publicError($errors, 422);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals($errors, (array)$response->getData()->errors);
    }

    /** @test */
    public function it_returns_not_authorized_response()
    {
        $response = $this->trait->publicNotAuthorized('Unauthorized.', 401);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('Unauthorized.', $response->getData()->errors->message);
        $this->assertEquals(401, $response->getData()->errors->status);
    }

    /** @test */
    public function it_returns_no_content_response()
    {
        $response = $this->trait->publicNoContent();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(204, $response->status());
        $this->assertEmpty($response->getContent());
    }
}