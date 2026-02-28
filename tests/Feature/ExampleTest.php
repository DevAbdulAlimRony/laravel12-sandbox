<?php

namespace Tests\Feature;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    // Unexpected behavior may occur if multiple requests are executed within a single test method.
    // For convenience, the CSRF middleware is automatically disabled when running tests.

    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $response = $this->withHeaders(['X-Header' => 'Value',])->post('/user', ['name' => 'Sally']);

        $this->withCookie('color', 'blue')->get('/')
        $this->withCookies([])
        $this->withSession(['banned' => false])->get('/')
    
       // Authenticate a given user as the current user: 
       $this->actingAs($user)->withSession(['banned' => false])->get();
       // Specify guard of authenticated user: 
       $this->actingAs($user, 'web')->get();
       // Unauthenticated Request: 
       $this->actingAsGuest();

        $response->dump();
        $response->dumpHeaders();
        $response->dumpSession();

        $response->dd();
        $response->ddHeaders();
        $response->ddBody();
        $response->ddJson();
        $response->ddSession();

        // $this->postJson('/api/user', ['name' => 'Sally']);
        // json(), getJson(0, putJson(), patchJson(), deleteJson(), optionsJson().
        // assertJson('created' => true), assertTrue($response['created']), assertExactJson(), assertJsonPath()

        // Testing Storage
        // Testing Views
        // Available Asserts, Authentication and Validation Assertions: https://laravel.com/docs/12.x/http-tests#available-assertions
    }

    public function test_exception_is_thrown(): void {
        Exceptions::fake();
        $response = $this->get('/');
        Exceptions::assertReported(InvalidOrderException::class);
        Exceptions::assertReported(function (InvalidOrderException $e) {
            return $e->getMessage() === 'The order was invalid.';
        });
        Exceptions::assertNotReported(InvalidOrderException::class);
        Exceptions::assertNothingReported();

        $this->withoutExceptionHandling()->get('/'); // totally disabled exception handling for this request.
        $this->withoutDeprecationHandling()->get('/'); // ensure that your application is not utilizing features that have been deprecated by the PHP language or used libraries.

        $this->assertThrows(
            fn () => (new ProcessOrder)->execute(),
            fn (OrderInvalid $e) => $e->orderId() === 123;
        );
        $this->assertDoesntThrow(fn () => (new ProcessOrder)->execute());
    }
}
