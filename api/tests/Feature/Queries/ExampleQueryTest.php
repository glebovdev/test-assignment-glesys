<?php

namespace Tests\Feature\Queries;

use Tests\TestCase;

class ExampleQueryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAsConsumer();
    }

    public function test_returns_numbers()
    {
        $query = <<<'GQL'
            query exampleQuery {
                exampleQuery {
                    value
                }
            }
            GQL;

        $this->graphql($query)
            ->assertOk()
            ->assertJsonPathCanonicalizing('data.exampleQuery.*.value', [1, 2, 3]);
    }

    public function test_returns_multipled_numbers_with_multiplyWith_argument()
    {
        $query = <<<'GQL'
            query exampleQuery {
                exampleQuery(multiplyWith: 5) {
                    value
                }
            }
            GQL;

        $this->graphql($query)
            ->assertOk()
            ->assertJsonPathCanonicalizing('data.exampleQuery.*.value', [5, 10, 15]);
    }
}
