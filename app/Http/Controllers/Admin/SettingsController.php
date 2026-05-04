<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function edit()
    {
        $settings = [
            'max_radius_km'    => Setting::get('max_radius_km', 10),
            'institution_name' => Setting::get('institution_name', 'Kampus'),
        ];

        return view('admin.settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'max_radius_km'    => ['required', 'numeric', 'min:0.1', 'max:1000'],
            'institution_name' => ['nullable', 'string', 'max:200'],
        ]);

        foreach ($data as $key => $value) {
            Setting::put($key, $value);
        }

        return back()->with('status', 'Pengaturan disimpan.');
    }
}
