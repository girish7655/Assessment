
<?php

namespace Tests\Unit\Http\Controllers;

use App\Http\Controllers\AuthorController;
use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthorControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new AuthorController();
    }

    public function test_store_creates_new_author_successfully()
    {
        $request = new Request();
        $request->merge(['name' => 'John Doe']);
        
        $response = $this->controller->store($request);
        
        $this->assertDatabaseHas('authors', ['name' => 'John Doe']);
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(route('authors.create'), $response->getTargetUrl());
        $this->assertEquals('Author created successfully!', session('success'));
    }

    public function test_store_prevents_duplicate_author_names()
    {
        Author::create(['name' => 'John Doe']);
        
        $request = new Request();
        $request->merge(['name' => 'John Doe']);
        
        $response = $this->controller->store($request);
        
        $this->assertEquals(1, Author::where('name', 'John Doe')->count());
        $this->assertEquals('This author name already exists.', session('error'));
    }

    public function test_store_validates_required_name()
    {
        $request = new Request();
        
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        
        $this->controller->store($request);
    }

    public function test_store_validates_name_format()
    {
        $request = new Request();
        $request->merge(['name' => 'John123 Doe']);
        
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        
        $this->controller->store($request);
    }

    public function test_store_validates_name_max_length()
    {
        $request = new Request();
        $request->merge(['name' => str_repeat('a', 256)]);
        
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        
        $this->controller->store($request);
    }

    public function test_store_handles_exception_gracefully()
    {
        $request = new Request();
        $request->merge(['name' => 'John Doe']);
        
        Author::shouldReceive('firstOrCreate')->andThrow(new \Exception());
        
        $response = $this->controller->store($request);
        
        $this->assertEquals('Failed to create Author. Please try again.', session('error'));
    }
}
