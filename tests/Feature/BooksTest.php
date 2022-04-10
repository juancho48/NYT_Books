<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Request;


class BooksTest extends TestCase
{
    protected $url = 'api/v1/nyt/books';
    protected $apiEndpoint = 'https://api.nytimes.com/svc/books/v3/lists/best-sellers/history.json';
    
    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        Http::fake(function (Request $request) {
            return Http::response([
                "status" => "OK",
                "copyright" => "Copyright (c) 2022 The New York Times Company.  All Rights Reserved.",
                "num_results" => 1,
                'results' => [$this->getBooksSample()],
            ], 200);
        });
    }

    /**
     * @return void
     */
    public function test_endpoint_works()
    {
        $response = $this->getJson('/api/v1/nyt/books');
        $response->assertStatus(200);
        $this->assertEquals([$this->getBooksSample()], $response->json());
    }

    /**
     * @return void
     */
    public function test_validation_author_should_be_string_255()
    {
        $response = $this->getJson('/api/v1/nyt/books?author=qcvvxlvlqjkadcfprkchdcwdaiitfkniznktltkvjnjpvzaegajggxhrbmiifomggygihbbvdonkzounelqzruxjuvqtbosysznclfyvxzjecmqqlbxcgyehxqbauvzbuwhropzvolwxsijfmtlupabbfqehmhtelqscsvvqejremqkbvwibzrsaxvdcnemawkwctfmuuvhlwnwzsvgcmtmbnvlzqjtvuxgajauizskmmweyzdrmpbgepqhjkkib');

        Http::assertNotSent(function (Request $request) {
            return $request->url() === $this->apiEndpoint;
        });
  
        $response->assertStatus(422)
                ->assertJsonPath('errors', ['author' => ['The author must not be greater than 255 characters.']]);
    }

    /**
     * @return void
     */
    public function test_validation_title_should_be_string_255()
    {
        $response = $this->getJson('/api/v1/nyt/books?title=qcvvxlvlqjkadcfprkchdcwdaiitfkniznktltkvjnjpvzaegajggxhrbmiifomggygihbbvdonkzounelqzruxjuvqtbosysznclfyvxzjecmqqlbxcgyehxqbauvzbuwhropzvolwxsijfmtlupabbfqehmhtelqscsvvqejremqkbvwibzrsaxvdcnemawkwctfmuuvhlwnwzsvgcmtmbnvlzqjtvuxgajauizskmmweyzdrmpbgepqhjkkib');

        Http::assertNotSent(function (Request $request) {
            return $request->url() === $this->apiEndpoint;
        });
  
        $response->assertStatus(422)
                ->assertJsonPath('errors', ['title' => ['The title must not be greater than 255 characters.']]);
    }

    /**
     * @return void
     */
    public function test_validation_isbn_correct_lenght()
    {
        $response = $this->getJson('/api/v1/nyt/books?isbn[]=x');

        Http::assertNotSent(function (Request $request) {
            return $request->url() === $this->apiEndpoint;
        });
  
        $response->assertStatus(422)
                ->assertJsonPath('errors', ['isbn[]' => ['ISBN must be a number 10 or 13 characters long']]);
    }

    /**
     * @return void
     */
    public function test_validation_isbn_is_array()
    {
        $response = $this->getJson('/api/v1/nyt/books?isbn=x');

        Http::assertNotSent(function (Request $request) {
            return $request->url() === $this->apiEndpoint;
        });

        $response->assertStatus(422)
                ->assertJsonPath('errors', [
                    'isbn'   => ['The isbn must be an array.'],
                    'isbn[]' => ['ISBN must be a number 10 or 13 characters long']
                ]);
    }

    /**
     * @return void
     */
    public function test_validation_offset_multiple_20()
    {
        $response = $this->getJson('/api/v1/nyt/books?offset=x');

        Http::assertNotSent(function (Request $request) {
            return $request->url() === $this->apiEndpoint;
        });

        $response->assertStatus(422)
                ->assertJsonPath('errors', [
                    'offset'   => ['Offset must be a number 0 or multiple of 20'],
                ]);
    }

    /**
     * @return void
     */
    public function test_validation_offset_valid_multiple_20()
    {
        $response = $this->getJson('/api/v1/nyt/books?offset=20');

        Http::assertNotSent(function (Request $request) {
            return $request->url() === $this->apiEndpoint;
        });

        $response->assertStatus(200);
        $this->assertEquals([$this->getBooksSample()], $response->json());
    }

    private function getBooksSample()
    {
        return [
            "title" => "AMERICAN GROWN",
            "description" => "The story of the White House kitchen garden and gardens across the country.",
            "contributor" => "by Michelle Obama",
            "author" => "Michelle Obama",
            "contributor_note" => "",
            "price" => "30.00",
            "age_group" => "",
            "publisher" => "Crown",
            "isbns" => [
                "isbn10" => "0307956024",
                "isbn13" => "9780307956026"
            ],
            "ranks_history" => [],
            "reviews" => [
                "book_review_link" => "",
                "first_chapter_link" => "",
                "sunday_review_link" => "",
                "article_chapter_link" => ""
            ]
        ];
    }

}
