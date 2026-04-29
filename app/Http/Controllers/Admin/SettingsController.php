<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'parish_name'    => config('parish.name'),
            'parish_address' => config('parish.address'),
            'parish_phone'   => config('parish.phone'),
            'parish_email'   => config('parish.email'),
            'parish_priest'  => config('parish.priest'),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'parish_name'    => ['required', 'string', 'max:255'],
            'parish_address' => ['required', 'string', 'max:255'],
            'parish_phone'   => ['nullable', 'string', 'max:50'],
            'parish_email'   => ['nullable', 'email'],
            'parish_priest'  => ['nullable', 'string', 'max:255'],
        ]);

        // Update .env file
        foreach ($validated as $key => $value) {
            $envKey = strtoupper($key);
            $this->updateEnv($envKey, $value);
        }

        Artisan::call('config:clear');

        return back()->with('success', 'Settings updated.');
    }

    public function clearCache(Request $request)
    {
        $type = $request->get('type', 'config');

        match ($type) {
            'view'   => Artisan::call('view:clear'),
            default  => Artisan::call('config:clear'),
        };

        return back()->with('success', ucfirst($type) . ' cache cleared.');
    }

    private function updateEnv(string $key, string $value): void
    {
        $path    = base_path('.env');
        $content = file_get_contents($path);

        if (str_contains($content, $key . '=')) {
            $content = preg_replace("/^{$key}=.*/m", "{$key}=\"{$value}\"", $content);
        } else {
            $content .= "\n{$key}=\"{$value}\"";
        }

        file_put_contents($path, $content);
    }
}
