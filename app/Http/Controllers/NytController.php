<?php

namespace App\Http\Controllers;

use App\Http\Requests\BooksRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;

class NytController extends Controller
{
    /**
     * @param BooksRequest $request
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function books(BooksRequest $request)
    {
        try {
            $response = Http::accept('application/json')
                ->get(config('services.nyt.endpoint') . 'lists/best-sellers/history.json', [
                'api-key'   => config('services.nyt.key'),
                'author'    => $request->input('author'),
                'title'     => $request->input('title'),
                // Found that their API has an error when searching for ISBNs. says to separate them with ; it doesn't work even in their api call page
                'isbn'      => $request->has('isbn') ? $request->input('isbn') : null,
                'offset'    => $request->has('offseet') ? $request->input('offset') : 0,
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'error' => 'Sorry we are unable to connect to the API at this time. ',
            ], 422);
        }

        $books = $response->json()['results'] ?? [];

        return Response::json($books);
    }
}
