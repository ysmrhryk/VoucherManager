<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\SettingsRequest;
use App\Models\Settings;

class SettingsController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show()
    {
        return response()->json(Settings::firstOrFail()); // 200
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SettingsRequest $request)
    {
        $settings = Settings::firstOrFail();
        
        $settings->update($request->validated());

        return response()->json($settings->fresh()); // 200 編集した新しいデータを返す（データベースの今の状態を返す
    }
}
