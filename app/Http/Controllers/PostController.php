<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class PostController extends Controller
{
    public function index()
    {
        try {
            $posts = Post::all();
            return response()->json($posts, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener los posts'], 500);
        }
    }

    public function show($id)
    {
        try {
            $post = Post::find($id);
            if (!$post) {
                return response()->json(['error' => 'Post no encontrado'], 404);
            }
            return response()->json($post, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error interno'], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'message' => 'required|string',
            'avatar'  => 'nullable|string|max:255',
            'image'   => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            $data = $request->only(['user_id', 'message', 'avatar']);

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('images', 'public');
                $data['image'] = asset('storage/' . $path);
            }

            $post = Post::create($data);

            return response()->json($post, 201);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return response()->json(['error' => 'No se pudo crear el post'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $post = Post::find($id);
            if (!$post) {
                return response()->json(['error' => 'Post no encontrado'], 404);
            }
            $post->update($request->all());
            return response()->json($post, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo actualizar el post'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $post = Post::find($id);
            if (!$post) {
                return response()->json(['error' => 'Post no encontrado'], 404);
            }
            $post->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo eliminar el post'], 500);
        }
    }

    public function comments($id)
    {
        try {
            $post = Post::find($id);
            if (!$post) {
                return response()->json(['error' => 'Post no encontrado'], 404);
            }
            $comments = $post->comments ?: [];
            return response()->json($comments, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener los comentarios'], 500);
        }
    }

    public function addComment(Request $request, $id)
    {
        try {
            $post = Post::find($id);
            if (!$post) {
                return response()->json(['error' => 'Post no encontrado'], 404);
            }

            $comments = $post->comments ?? [];

            $newComment = [
                'id' => (string) Str::uuid(),
                'username' => $request->input('username', 'Unknown'),
                'comment' => $request->input('comment', ''),
                'created_at' => now()->toDateTimeString(),
            ];

            $comments[] = $newComment;

            $post->comments = $comments;
            $post->save();
            return response()->json($newComment, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo agregar el comentario'], 500);
        }
    }

    public function updateComment(Request $request, $id, $uid)
    {
        try {
            $post = Post::find($id);
            if (!$post) {
                return response()->json(['error' => 'Post no encontrado'], 404);
            }

            $comments = $post->comments ?: [];
            $found = false;

            foreach ($comments as &$comment) {
                if ($comment['id'] === $uid) {
                    $comment['comment'] = $request->input('comment', $comment['comment']);
                    if ($request->has('username')) {
                        $comment['username'] = $request->input('username', $comment['username']);
                    }
                    $comment['updated_at'] = now()->toDateTimeString();
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                return response()->json(['error' => 'Comentario no encontrado'], 404);
            }

            $post->comments = $comments;
            $saved = $post->save();

            if (!$saved) {
                return response()->json(['error' => 'No se pudo guardar los cambios'], 500);
            }

            return response()->json(['message' => 'Comentario actualizado'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo actualizar el comentario'], 500);
        }
    }

    public function deleteComment($id, $commentId)
    {
        try {
            $post = Post::find($id);
            if (!$post) {
                return response()->json(['error' => 'Post no encontrado'], 404);
            }

            $comments = $post->comments ?: [];
            $initialCount = count($comments);


            $comments = array_filter($comments, function ($comment) use ($commentId) {
                return $comment['id'] !== $commentId;
            });

            if (count($comments) === $initialCount) {
                return response()->json(['error' => 'Comentario no encontrado'], 404);
            }

            $comments = array_values($comments);

            $post->comments = $comments;
            $saved = $post->save();

            if (!$saved) {
                return response()->json(['error' => 'No se pudo guardar los cambios'], 500);
            }

            return response()->json(['message' => 'Comentario eliminado'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo eliminar el comentario'], 500);
        }
    }


    public function like($id)
    {
        try {
            $post = Post::find($id);
            if (!$post) {
                return response()->json(['error' => 'Post no encontrado'], 404);
            }
            $post->likes++;
            $post->save();

            return response()->json(['likes' => $post->likes], 204);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo dar like al post'], 500);
        }
    }

    public function removelike($id)
    {
        try {
            $post = Post::find($id);
            if (!$post) {
                return response()->json(['error' => 'Post no encontrado'], 404);
            }

            if ($post->likes > 0) {
                $post->likes--;
            }
            $post->save();

            return response()->json(['likes' => $post->likes], 204);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo dar like al post'], 500);
        }
    }
}
