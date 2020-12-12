<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redis;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {

    $visits = Redis::incr('visits');

    return view('welcome')->withVisits($visits);
});
Route::get('/fire', function () {
    event(new \App\Events\TestEvent());
    return 'ok';
});
Route::get('articles/trending', function () {

    $trending   =   Redis::zrevrange('trending_articles',0,2);

    dd($trending);
});
Route::get('videos/{id}', function ($id) {

    $downloads = Redis::get('videos.'.$id.'.downloads');

    return view('test')->withDownloads($downloads);
});
Route::get('videos/{id}/download', function ($id) {

    Redis::incr('videos.'.$id.'.downloads');

    return back();
});

Route::get('articles/{article}', function (\App\Models\Article $article) {

    Redis::zincrby('trending_articles',1,$article->id);

    Redis::zremrangebyrank('trending_articles',0,-4);

    return $article;
});

/*Redis Caching*/
Route::get('caching-articles', function () {
    if ($value = Redis::get('articles.all')){
        return unserialize($value);
    }
    $articles   =   \App\Models\Article::all();

    Redis::set('articles.all',serialize($articles));

    return $articles;
});
/*Redis Caching Using Laravel Driver*/
Route::get('laravel-caching-articles', function () {
    return Cache::remember('articles.all',30,function (){
        return \App\Models\Article::all();
    });
});
