<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Publisher;

class PublisherControllerTest extends TestCase
{
    use RefreshDatabase; // Ensures fresh DB for each test run

    /**
     * Test fetching publisher list (index method).
     */
    public function test_publisher_list_is_fetched_successfully()
    {
        // Create test publishers
        Publisher::factory()->count(3)->create();

        // Make a GET request to the index route
        $response = $this->get(route('publishers.index'));

        // Assertions
        $response->assertStatus(200);
        $response->assertSee('Publishers List Fetched Successfully');
    }

    /**
     * Test creating a new publisher successfully.
     */
    public function test_create_new_publisher_successfully()
    {
        // Mock publisher data
        $publisherData = [
            'name' => 'Test Publisher'
        ];

        // Make a POST request
        $response = $this->post(route('publishers.store'), $publisherData);

        // Assertions
        $response->assertRedirect(route('publishers.create'));
        $this->assertDatabaseHas('publishers', ['name' => 'Test Publisher']);
    }

    /**
     * Test publisher creation fails with missing required fields.
     */
    public function test_create_publisher_fails_due_to_missing_fields()
    {
        $response = $this->post(route('publishers.store'), []);

        // Assertions
        $response->assertSessionHasErrors(['name']);
    }

    /**
     * Test publisher creation fails due to invalid characters.
     */
    public function test_create_publisher_fails_due_to_invalid_characters()
    {
        $response = $this->post(route('publishers.store'), [
            'name' => 'Publisher 123!' // Invalid due to regex
        ]);

        // Assertions
        $response->assertSessionHasErrors(['name']);
    }

    /**
     * Test updating a publisher successfully.
     */
    public function test_update_publisher_successfully()
    {
        // Create a sample publisher
        $publisher = Publisher::factory()->create(['name' => 'Old Publisher']);

        // Updated publisher data
        $updatedData = [
            'name' => 'Updated Publisher'
        ];

        // Make a PUT request
        $response = $this->put(route('publishers.update', $publisher->id), $updatedData);

        // Assertions
        $response->assertRedirect(route('publishers.edit', $publisher->id));
        $this->assertDatabaseHas('publishers', ['name' => 'Updated Publisher']);
    }

    /**
     * Test updating a publisher fails due to duplicate name.
     */
    public function test_update_publisher_fails_due_to_duplicate_name()
    {
        // Create two sample publishers
        $publisher1 = Publisher::factory()->create(['name' => 'Existing Publisher']);
        $publisher2 = Publisher::factory()->create(['name' => 'Another Publisher']);

        // Attempt to update publisher2 with publisher1's name
        $response = $this->put(route('publishers.update', $publisher2->id), [
            'name' => 'Existing Publisher'
        ]);

        // Assertions
        $response->assertSessionHasErrors(['name']);
    }

    /**
     * Test deleting a publisher successfully.
     */
    public function test_delete_publisher_successfully()
    {
        // Create a sample publisher
        $publisher = Publisher::factory()->create();

        // Make a DELETE request
        $response = $this->delete(route('publishers.destroy', $publisher->id));

        // Assertions
        $response->assertRedirect(route('publishers.index'));
        $this->assertDatabaseMissing('publishers', ['id' => $publisher->id]);
    }
}
