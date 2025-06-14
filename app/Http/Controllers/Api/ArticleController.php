<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Article;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ArticleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/articles",
     *     summary="Get all articles",
     *     tags={"Articles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="category_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="limit", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Success"),
     * )
     */

    public function index(Request $request)
    {
        $query = Article::with(['category', 'user']);

        if ($search = $request->query('search')) {
            $query->where('title', 'like', "%$search%");
        }

        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        }

        $limit = $request->query('limit', 10);

        $articles = $query->orderByDesc('created_at')->paginate($limit);

        return ResponseFormatter::success($articles, 'Articles retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/articles/{id}",
     *     summary="Get article by ID",
     *     tags={"Articles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Success"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */

    public function show($id)
    {
        $article = Article::with(['category', 'user'])->where('id', $id)->first();

        if (!$article) {
            return response()->json(['message' => 'Article not found'], 404);
        }

        return ResponseFormatter::success($article, 'Article retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/articles/slug/{slug}",
     *     summary="Get article by slug",
     *     tags={"Articles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Success"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */

    public function showBySlug($slug)
    {
        $article = Article::with(['category', 'user'])->where('slug', $slug)->first();

        if (!$article) {
            return response()->json(['message' => 'Article not found'], 404);
        }

        return ResponseFormatter::success($article, 'Article retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/articles",
     *     summary="Create new article",
     *     tags={"Articles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title", "content", "category_id"},
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="content", type="string"),
     *                 @OA\Property(property="category_id", type="integer"),
     *                 @OA\Property(property="thumbnail", type="file")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Article created"),
     *     @OA\Response(response=422, description="Validation failed")
     * )
     */

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'title' => 'required|string|unique:articles,title',
                'content' => 'required|string',
                'category_id' => 'required|exists:categories,id',
                'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            $slug = Str::slug($data['title']);

            $thumbnailPath = $request->hasFile('thumbnail')
                ? $request->file('thumbnail')->store('assets/thumbnail', 'public')
                : null;

            $article = Article::create([
                'title' => $data['title'],
                'slug' => $slug,
                'content' => $data['content'],
                'thumbnail' => $thumbnailPath,
                'category_id' => $data['category_id'],
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'meta' => [
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Article created successfully',
                ],
                'data' => $article,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'meta' => [
                    'code' => 422,
                    'status' => 'fail',
                    'message' => 'Validation failed',
                ],
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Article creation error: ' . $e->getMessage());

            return response()->json([
                'meta' => [
                    'code' => 500,
                    'status' => 'error',
                    'message' => 'Something went wrong: ' . $e->getMessage(),
                ],
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/articles/{id}",
     *     summary="Update article",
     *     tags={"Articles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title", "content", "category_id"},
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="content", type="string"),
     *                 @OA\Property(property="category_id", type="integer"),
     *                 @OA\Property(property="thumbnail", type="file")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Article updated"),
     *     @OA\Response(response=422, description="Validation failed")
     * )
     */

    public function update(Request $request, $id)
    {
        try {
            $article = Article::findOrFail($id);

            $data = $request->validate([
                'title' => 'required|string|unique:articles,title,' . $article->id,
                'content' => 'required|string',
                'category_id' => 'required|exists:categories,id',
                'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
            ]);

            $slug = Str::slug($data['title']);
            $newThumbnail = $article->thumbnail;

            if ($request->hasFile('thumbnail')) {
                // Hapus thumbnail lama
                if ($article->thumbnail && Storage::disk('public')->exists($article->thumbnail)) {
                    Storage::disk('public')->delete($article->thumbnail);
                }

                // Simpan thumbnail baru
                $newThumbnail = $request->file('thumbnail')->store('assets/thumbnail', 'public');
            }

            $article->update([
                'title' => $data['title'],
                'slug' => $slug,
                'user_id' => Auth::id(),
                'content' => $data['content'],
                'thumbnail' => $newThumbnail,
                'category_id' => $data['category_id'],
            ]);

            return ResponseFormatter::success($article, 'Article updated successfully');
        } catch (ValidationException $e) {
            return ResponseFormatter::error($e->errors(), 'Validation failed', 422);
        } catch (\Exception $e) {
            Log::error('Article update error: ' . $e->getMessage());
            return ResponseFormatter::error(null, 'Failed to update article', 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/articles/{id}",
     *     summary="Delete article",
     *     tags={"Articles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Article deleted")
     * )
     */

    public function destroy($id)
    {
        try {
            $article = Article::findOrFail($id);

            if ($article->thumbnail && Storage::disk('public')->exists($article->thumbnail)) {
                Storage::disk('public')->delete($article->thumbnail);
            }

            $article->delete();

            return ResponseFormatter::success(null, 'Article deleted successfully');
        } catch (\Exception $e) {
            Log::error('Article delete error: ' . $e->getMessage());
            return ResponseFormatter::error(null, 'Failed to delete article', 500);
        }
    }
}
