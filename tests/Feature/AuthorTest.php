<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\Author;

class AuthorTest extends TestCase
{
    use DatabaseMigrations;

    public function test_it_returns_an_author_as_a_resource_object()
    {
        $author = Author::factory()->create();
        $this->getJson('/api/authors/1', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200)->assertJson([
            "data" => [
                "id" => '1',
                "type" => "authors",
                "attributes" => [
                    'name' => $author->name,
                    'created_at' => $author->created_at->toJSON(),
                    'updated_at' => $author->updated_at->toJSON(),
                ]
            ]
        ]);
    }

    public function test_it_returns_all_authors_as_a_collection_of_resource_objects()
    {
        $authors = Author::factory(3)->create();
        $this->get('/api/authors', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200)->assertJson([
            "data" => [
                [
                    "id" => '1',
                    "type" => "authors",
                    "attributes" => [
                        'name' => $authors[0]->name,
                        'created_at' => $authors[0]->created_at->toJSON(),
                        'updated_at' => $authors[0]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '2',
                    "type" => "authors",
                    "attributes" => [
                        'name' => $authors[1]->name,
                        'created_at' => $authors[1]->created_at->toJSON(),
                        'updated_at' => $authors[1]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '3',
                    "type" => "authors",
                    "attributes" => [
                        'name' => $authors[2]->name,
                        'created_at' => $authors[2]->created_at->toJSON(),
                        'updated_at' => $authors[2]->updated_at->toJSON(),
                    ]
                ],
            ]
        ]);
    }

    public function test_it_can_create_an_author_from_a_resource_object()
    {
        $this->postJson('/api/authors', [
            'data' => [
                'type' => 'authors',
                'attributes' => [
                    'name' => 'John Doe',
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(201)->assertJson([
            "data" => [
                "id" => '1',
                "type" => "authors",
                "attributes" => [
                    'name' => 'John Doe',
                    'created_at' => now()->setMillisecond(0)->toJSON(),
                    'updated_at' => now()->setMillisecond(0)->toJSON(),
                ]
            ]
        ])->assertHeader('Location', url('/api/authors/1'));

        $this->assertDatabaseHas('authors', [
            'id' => 1,
            'name' => 'John Doe'
        ]);
    }

    public function test_it_validates_that_the_type_member_is_given_when_creating_an_author()
    {
        $author = Author::factory()->create();
        $this->postJson('/api/authors', [
            'data' => [
                'type' => '',
                'attributes' => [
                    'name' => 'John Doe',
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(422)->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.type field is required.',
                    'source' => [
                        'pointer' => '/data/type',
                    ]
                ]
            ]
        ]);
        $this->assertDatabaseMissing('authors', [
            'id' => 1,
            'name' => 'John Doe'
        ]);
    }

    public function test_it_validates_that_the_type_member_has_the_value_of_authors_when_creating_an_author()
    {
        $this->postJson('/api/authors', [
            'data' => [
                'type' => 'author',
                'attributes' => [
                    'name' => 'John Doe',
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(422)->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The selected data.type is invalid.',
                    'source' => [
                        'pointer' => '/data/type',
                    ]
                ]
            ]
        ]);

        $this->assertDatabaseMissing('authors', [
            'id' => 1,
            'name' => 'John Doe'
        ]);

    }

    public function test_it_can_update_an_author_from_a_resource_object()
    {
        $author = Author::factory()->create();
        $this->patchJson('/api/authors/1', [
            'data' => [
                'id' => '1',
                'type' => 'authors',
                'attributes' => [
                    'name' => 'Jane Doe',
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200)->assertJson([
            'data' => [
                'id' => '1',
                'type' => 'authors',
                'attributes' => [
                    'name' => 'Jane Doe',
                    'created_at' => now()->setMillisecond(0)->toJSON(),
                    'updated_at' => now()->setMillisecond(0)->toJSON(),
                ],
            ]
        ]);
        $this->assertDatabaseHas('authors', [
            'id' => 1,
            'name' => 'Jane Doe',
        ]);
    }

    public function test_it_can_delete_an_author_through_a_delete_request()
    {
        $author = Author::factory()->create();
        $this->delete('/api/authors/1', [], [
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
        ])->assertStatus(204);

        $this->assertDatabaseMissing('authors', [
            'id' => 1,
            'name' => $author->name,
        ]);
    }

    public function test_it_can_sort_authors_by_name_through_a_sort_query_parameter()
    {
        $authors = collect([
            'Bertram',
            'Claus',
            'Anna',
        ])->map(function ($name) {
            return Author::factory()->create([
                'name' => $name
            ]);
        });
        $this->get('/api/authors?sort=name', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200)->assertJson([
            "data" => [
                [
                    "id" => '3',
                    "type" => "authors",
                    "attributes" => [
                        'name' => 'Anna',
                        'created_at' => $authors[2]->created_at->toJSON(),
                        'updated_at' => $authors[2]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '1',
                    "type" => "authors",
                    "attributes" => [
                        'name' => 'Bertram',
                        'created_at' => $authors[0]->created_at->toJSON(),
                        'updated_at' => $authors[0]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '2',
                    "type" => "authors",
                    "attributes" => [
                        'name' => 'Claus',
                        'created_at' => $authors[1]->created_at->toJSON(),
                        'updated_at' => $authors[1]->updated_at->toJSON(),
                    ]
                ],
            ]
        ]);
    }

    public function test_it_can_sort_authors_by_name_in_descending_order_through_a_sort_query_parameter()
    {
        $authors = collect([
            'Bertram',
            'Claus',
            'Anna',
        ])->map(function ($name) {
            return Author::factory()->create([
                'name' => $name
            ]);
        });
        $this->get('/api/authors?sort=-name', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200)->assertJson([
            "data" => [
                [
                    "id" => '2',
                    "type" => "authors",
                    "attributes" => [
                        'name' => 'Claus',
                        'created_at' => $authors[1]->created_at->toJSON(),
                        'updated_at' => $authors[1]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '1',
                    "type" => "authors",
                    "attributes" => [
                        'name' => 'Bertram',
                        'created_at' => $authors[0]->created_at->toJSON(),
                        'updated_at' => $authors[0]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '3',
                    "type" => "authors",
                    "attributes" => [
                        'name' => 'Anna',
                        'created_at' => $authors[2]->created_at->toJSON(),
                        'updated_at' => $authors[2]->updated_at->toJSON(),
                    ]
                ],
            ]
        ]);
    }
}
