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
        $post = new Post($request->all());
        $post->user_id = $request->user()->id;

        $files = $request->image;

        // $request->validate([
        //     'image' => 'required',
        //     'image.*' => 'required|file|image|mimes:jpeg,png'
        // ]);

        DB::beginTransaction();
        try {
            $post->save();

            $paths = [];
            
            foreach ($files as $file) {
                $path = Storage::putFile('posts', $file);

                if (!$path) {
                    throw new Exception('ファイルの保存に失敗しました');
                }

                $paths[] = $path;

                $image = new Image([
                    'post_id' => $post->id,
                    'image' => basename($path),
                ]);
                $image->save();
            }
            DB::commit();
        } catch (\Exception $e) {
            if (!empty($path)) {
                foreach ($paths as $path) {
                    Storage::delete($path);
                }
            }
            DB::rollBack();
            return back()->withInput()->withErrors($e->getMessage());
        }

        return redirect()->route('posts.index')
            ->with(['notice' => '登録が完了しました']);
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
            ->with(['notice' => '更新が完了しました']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        $delete_file_paths = $post->image_paths;
        DB::beginTransaction();
        try {
            // dd($post->image_path);
            $post->delete();

            foreach ($delete_file_paths as $delete_file_path) {
                if (!Storage::delete($delete_file_path)) {
                    throw new \Exception('ファイル削除に失敗しました');
                }
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors($e->getMessage());
        }

        return redirect()->route('posts.index')->with('notice', '投稿を削除しました');
    }
}
