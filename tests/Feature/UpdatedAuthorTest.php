<?php

namespace Tests\Unit\Http\Controllers;

use App\Http\Controllers\AuthorController;
use App\Models\Author;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;
use Illuminate\Validation\ValidationException;

class AuthorControllerTest extends TestCase
{
    use RefreshDatabase;

    private AuthorController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new AuthorController();
    }

    public function testUpdateWithValidData()
    {
        $author = Author::factory()->create(['name' => 'Old Name']);
        $request = new Request();
        $request->merge(['name' => 'New Valid Name']);

        $response = $this->controller->update($request, $author->id);

        $this->assertDatabaseHas('authors', [
            'id' => $author->id,
            'name' => 'New Valid Name'
        ]);
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(route('authors.edit', ['author' => $author->id]), $response->getTargetUrl());
    }

    public function testUpdateWithInvalidName()
    {
        $author = Author::factory()->create(['name' => 'Old Name']);
        $request = new Request();
        $request->merge(['name' => 'Invalid123Name']);

        $this->expectException(ValidationException::class);
        $this->controller->update($request, $author->id);
    }

    public function testUpdateWithDuplicateName()
    {
        $existingAuthor = Author::factory()->create(['name' => 'Existing Name']);
        $authorToUpdate = Author::factory()->create(['name' => 'Old Name']);
        
        $request = new Request();
        $request->merge(['name' => 'Existing Name']);

        $this->expectException(ValidationException::class);
        $this->controller->update($request, $authorToUpdate->id);
    }

    public function testUpdateWithNonExistentAuthor()
    {
        $request = new Request();
        $request->merge(['name' => 'Valid Name']);

        $response = $this->controller->update($request, '999');

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue(session()->has('error'));
    }

    public function testUpdateWithEmptyName()
    {
        $author = Author::factory()->create(['name' => 'Old Name']);
        $request = new Request();
        $request->merge(['name' => '']);

        $this->expectException(ValidationException::class);
        $this->controller->update($request, $author->id);
    }

    public function testUpdateWithTooLongName()
    {
        $author = Author::factory()->create(['name' => 'Old Name']);
        $request = new Request();
        $request->merge(['name' => str_repeat('a', 256)]);

        $this->expectException(ValidationException::class);
        $this->controller->update($request, $author->id);
    }
}
