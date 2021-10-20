<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Http\Requests\PostRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Image;
use Exception;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts = Post::all();
        return view('posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('posts.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\PostRequest;  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PostRequest $request)
    {
        dd($request->file('image'));
        $post = new Post($request->all());
        $post->user_id = $request->user()->id;

        $files = $request->file('image');

        if (!$files = $request->file('image')) {
            throw new Exception('ファイルの保存に失敗しました');
        }


        
        DB::beginTransaction();
        try {
            $post->save();
            foreach ($files as $file) {
                $path = Storage::putFile('posts', $file);
            }

            $image = new Image([
                'post_id' => $post->id,
                'image' => basename($path),
            ]);

            $image->save();

            DB::commit();
        } catch (\Exception $e) {
            if (Storage::exists($path)) {
                Storage::delete($path);
            }
            DB::rollBack();
            return back()->withInput()->withErrors($e->getMessage());
        }

        return redirect()->route('posts.index')
            ->with(['flash_message' => '登録が完了しました']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        return view('posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function edit(Post $post)
    {
        return view('posts.edit', compact('post'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\PostRequest;  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(PostRequest $request, Post $post)
    {
        $post->fill($request->all());

        try {
            $post->save();
        } catch (\Exception $e) {
            return back()->withInput()->withErrors($e->getMessage());
        }

        return redirect()->route('posts.show', $post)
        ->with(['flash_message' => '更新が完了しました']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        $path = $post->image_path;
        DB::beginTransaction();
        try {
            $post->delete();
            $post->image->delete();
            if (!Storage::delete($path)) {
                throw new \Exception('画像ファイルの削除に失敗しました');
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            return back()->withInput()->withErrors($e->getMessage());
        }

        return redirect()->route('posts.index')->with('flash_message', '投稿を削除しました');
    }
}
