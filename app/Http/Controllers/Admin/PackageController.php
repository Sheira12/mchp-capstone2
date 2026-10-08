<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServicePackage;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $services = Service::where('is_active', true)->orderBy('name')->get();

        $query = ServicePackage::with('service');
        if ($svc = $request->get('service_id')) {
            $query->where('service_id', $svc);
        }

        $packages = $query->orderBy('service_id')->orderBy('sort_order')->paginate(30)->withQueryString();

        return view('admin.packages.index', compact('packages', 'services'));
    }

    public function create()
    {
        $services = Service::where('is_active', true)->orderBy('name')->get();
        return view('admin.packages.create', compact('services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id'  => ['required', 'exists:services,id'],
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'inclusions'  => ['nullable', 'string'],   // newline-separated list
            'price'       => ['required', 'numeric', 'min:0'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['boolean'],
        ]);

        $validated['inclusions'] = $this->parseInclusions($request->get('inclusions', ''));
        $validated['is_active']  = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        ServicePackage::create($validated);

        return redirect()->route('admin.packages.index')->with('success', 'Package created successfully.');
    }

    public function edit(ServicePackage $package)
    {
        $services = Service::where('is_active', true)->orderBy('name')->get();
        return view('admin.packages.edit', compact('package', 'services'));
    }

    public function update(Request $request, ServicePackage $package)
    {
        $validated = $request->validate([
            'service_id'  => ['required', 'exists:services,id'],
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'inclusions'  => ['nullable', 'string'],
            'price'       => ['required', 'numeric', 'min:0'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['boolean'],
        ]);

        $validated['inclusions'] = $this->parseInclusions($request->get('inclusions', ''));
        $validated['is_active']  = $request->boolean('is_active');

        $package->update($validated);

        return redirect()->route('admin.packages.index')->with('success', 'Package updated.');
    }

    public function destroy(ServicePackage $package)
    {
        $package->update(['is_active' => false]);
        return redirect()->route('admin.packages.index')->with('success', 'Package deactivated.');
    }

    /** API: return packages for a given service slug (used by booking form JS). */
    public function forService(Request $request)
    {
        $service  = \App\Models\Service::where('slug', $request->get('slug'))->first();
        if (!$service) return response()->json([]);

        $packages = $service->packages()->get()->map(fn($p) => [
            'id'          => $p->id,
            'name'        => $p->name,
            'description' => $p->description,
            'inclusions'  => $p->inclusionsList(),
            'price'       => (float) $p->price,
            'price_fmt'   => $p->priceFormatted(),
        ]);

        return response()->json($packages);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Parse textarea (one inclusion per line) into array. */
    private function parseInclusions(string $raw): array
    {
        return collect(explode("\n", $raw))
            ->map(fn($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }
}
