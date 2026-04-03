<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\{ StoreUserRequest, UpdateUserRequest, UpdatePasswordUserRequest };
use Spatie\QueryBuilder\{ QueryBuilder, AllowedFilter };
use App\Filters\UserKeywordSearch;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = QueryBuilder::for(User::class)
        ->allowedFilters([
            AllowedFilter::custom('keywords', new UserKeywordSearch()), // キーワード検索
            'code',
            'name',
            'email'
        ])
        ->defaultSort('code')
        ->paginate(100); // 一ページ当たり100件

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->validated())->fresh();

        return response()->json($user, 201); // 201 作成したデータを返す
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return response()->json($user); // 200
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->validated());

        return response()->json($user->fresh()); // 200 編集した新しいデータを返す（データベースの今の状態を返す
    }

    public function updatePassword(UpdatePasswordUserRequest $request, User $user)
    {
        $user->update($request->validated());

        return response()->json($user->fresh()); // 200 編集した新しいデータを返す（データベースの今の状態を返す
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->noContent(); // 204
        // もしかしたら削除したデータに関して何か返したほうが、フロントが楽になるかもしれない
    }
}
