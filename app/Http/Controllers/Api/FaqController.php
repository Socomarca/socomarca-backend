<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Faq\DestroyRequest;
use App\Http\Requests\Faq\SearchRequest;
use App\Http\Requests\Faq\StoreRequest;
use App\Http\Requests\Faq\UpdateRequest;
use App\Http\Resources\Faq\FaqCollection;
use App\Http\Resources\Faq\FaqResource;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $faqs = Faq::orderBy('created_at', 'desc')->paginate($perPage);

        return new FaqCollection($faqs);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $faq = Faq::create($request->validated());

        return (new FaqResource($faq))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Faq $faq)
    {
        return new FaqResource($faq);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, Faq $faq)
    {
        $faq->update($request->validated());

        return new FaqResource($faq);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DestroyRequest $request, Faq $faq)
    {
        $faq->delete();

        return response()->json([
            'message' => 'FAQ eliminada exitosamente.'
        ]);
    }


    public function search(SearchRequest $request)
    {
        $perPage = $request->input('per_page', 20);
        $search = $request->input('search');
        $filters = $request->input('filters', []);

        $query = Faq::query();

        if ($search) {
            $query->search($search);
        }

        if (!empty($filters)) {
            $query->filter($filters);
        }

        $faqs = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return new FaqCollection($faqs);
    }
}
