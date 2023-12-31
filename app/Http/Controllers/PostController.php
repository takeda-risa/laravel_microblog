<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\PostRequest;
use App\Models\Post;
use App\Models\User;



class PostController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    // 投稿一覧
    // public function index(){
    //     $user = \Auth::user();
    //     $follow_user_ids = $user->follow_users->pluck('id');
    //     $user_posts = $user->posts()->orWhereIn('user_id', $follow_user_ids )->latest()->get();
    //     $recommended_users = User::recommend($user->id)->whereNotIn('id' , $follow_user_ids)->latest()->get();
        
        
    //     return view('posts.index', [
    //         'title' => '投稿一覧',
    //         'posts' => $user_posts,
    //         // 'recommended_users' => User::recommend($user->id)->latest()->get(),
    //         'recommended_users' => $recommended_users,
    //         'user' => $user,
    //     ]);
    // }
    public function index(Request $request){
        $user = \Auth::user();
        $follow_user_ids = $user->follow_users->pluck('id');
        // $user_posts = $user->posts()->orWhereIn('user_id', $follow_user_ids )->latest()->get();
        $recommended_users = User::recommend($user->id)->whereNotIn('id' , $follow_user_ids)->latest()->get();
        
        $keyword = $request->input('keyword');

        $query = Post::query();

        if(!empty($keyword)) {
            $user_posts = $query->where('comment', 'LIKE', "%{$keyword}%")->where('user_id', '!=', $user->id)->latest()->get();
        }
        else{
            $user_posts = $user->posts()->orWhereIn('user_id', $follow_user_ids )->latest()->get();
        }

        // $user_posts = $query->latest()->get();     
        
        return view('posts.index', [
            'title' => '投稿一覧',
            'posts' => $user_posts,
            // 'recommended_users' => User::recommend($user->id)->latest()->get(),
            'recommended_users' => $recommended_users,
            'user' => $user,
            'keyword' => $keyword, 
        ]);
    }


    // 新規投稿フォーム
    public function create()
    {
        return view('posts.create', [
          'title' => '新規投稿',
        ]);
    }

   // 投稿追加処理
    public function store(PostRequest $request)
    {
        Post::create([
          'user_id' => \Auth::user()->id,
          'comment' => $request->comment,
        ]);
        \Session::flash('success', '記事を投稿しました');
        return redirect('/posts');
    }

    // 投稿詳細
    public function show($id)
    {
        return view('posts.show', [
          'title' => '投稿詳細',
        //   'recommended_posts' => Post::recommend($user->id)->get()
        ]);
    }

    // 投稿編集フォーム
    public function edit(Post $post)
    {
        // ルーティングパラメータで渡されたidを利用してインスタンスを取得
        // $post = Post::find($id); //不要になる！
        return view('posts.edit', [
          'title' => '投稿編集',
          'post' => $post,
        ]);
    }

    // 投稿更新処理
    public function update(PostRequest $request, $id)
    {
        $post = Post::find($id);
        $post->update($request->only(['comment']));
        session()->flash('success','投稿を編集しました');
        return redirect()->route('posts.index');
    }

    // 投稿削除処理
    public function destroy($id)
    {
        $post = Post::find($id);
        
        $post->delete();
        \Session::flash('success', '投稿を削除しました');
        return redirect()->route('posts.index');
    }
}
