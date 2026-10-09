<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EligibilityRule;
use App\Models\Service;
use Illuminate\Http\Request;

class EligibilityRuleController extends Controller
{
    public function index(Service $service)
    {
        $rules = $service->allEligibilityRules()->with('service')->get();
        return view('admin.eligibility-rules.index', compact('service', 'rules'));
    }

    public function create(Service $service)
    {
        return view('admin.eligibility-rules.create', compact('service'));
    }

    public function store(Request $request, Service $service)
    {
        $validated = $request->validate([
            'criterion_type' => ['required', 'in:' . implode(',', array_keys(EligibilityRule::CRITERION_TYPES))],
            'applies_to'     => ['required', 'in:' . implode(',', array_keys(EligibilityRule::APPLIES_TO_LABELS))],
            'name'           => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string', 'max:1000'],
            'is_required'    => ['boolean'],
            'sort_order'     => ['nullable', 'integer', 'min:0'],
            // params sub-fields
            'param_service_slug'    => ['nullable', 'string'],
            'param_validity_days'   => ['nullable', 'integer', 'min:1'],
            'param_document_key'    => ['nullable', 'string', 'max:255'],
            'param_sacrament_type'  => ['nullable', 'string'],
            'param_min_age'         => ['nullable', 'integer', 'min:0', 'max:120'],
        ]);

        $params = $this->buildParams($request, $validated['criterion_type']);

        EligibilityRule::create([
            'service_id'     => $service->id,
            'criterion_type' => $validated['criterion_type'],
            'applies_to'     => $validated['applies_to'],
            'params'         => $params,
            'name'           => $validated['name'],
            'description'    => $validated['description'],
            'is_required'    => $request->boolean('is_required', true),
            'is_placeholder' => false, // admin-created rules are NOT placeholders
            'is_active'      => true,
            'sort_order'     => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.eligibility-rules.index', $service)
            ->with('success', 'Eligibility rule added.');
    }

    public function edit(Service $service, EligibilityRule $eligibilityRule)
    {
        return view('admin.eligibility-rules.edit', compact('service', 'eligibilityRule'));
    }

    public function update(Request $request, Service $service, EligibilityRule $eligibilityRule)
    {
        $validated = $request->validate([
            'criterion_type' => ['required', 'in:' . implode(',', array_keys(EligibilityRule::CRITERION_TYPES))],
            'applies_to'     => ['required', 'in:' . implode(',', array_keys(EligibilityRule::APPLIES_TO_LABELS))],
            'name'           => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string', 'max:1000'],
            'is_required'    => ['boolean'],
            'is_active'      => ['boolean'],
            'sort_order'     => ['nullable', 'integer', 'min:0'],
            'param_service_slug'    => ['nullable', 'string'],
            'param_validity_days'   => ['nullable', 'integer', 'min:1'],
            'param_document_key'    => ['nullable', 'string', 'max:255'],
            'param_sacrament_type'  => ['nullable', 'string'],
            'param_min_age'         => ['nullable', 'integer', 'min:0', 'max:120'],
        ]);

        $params = $this->buildParams($request, $validated['criterion_type']);

        $eligibilityRule->update([
            'criterion_type' => $validated['criterion_type'],
            'applies_to'     => $validated['applies_to'],
            'params'         => $params,
            'name'           => $validated['name'],
            'description'    => $validated['description'],
            'is_required'    => $request->boolean('is_required'),
            'is_active'      => $request->boolean('is_active'),
            'is_placeholder' => false, // once edited by admin it's no longer a placeholder
            'sort_order'     => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.eligibility-rules.index', $service)
            ->with('success', 'Rule updated.');
    }

    public function destroy(Service $service, EligibilityRule $eligibilityRule)
    {
        $eligibilityRule->update(['is_active' => false]);
        return redirect()->route('admin.eligibility-rules.index', $service)
            ->with('success', 'Rule deactivated.');
    }

    private function buildParams(Request $request, string $criterionType): array
    {
        return match ($criterionType) {
            'seminar_completed' => [
                'service_slug'  => $request->get('param_service_slug') ?: null,
                'validity_days' => $request->get('param_validity_days') ? (int)$request->get('param_validity_days') : null,
            ],
            'document_approved' => [
                'document_key' => $request->get('param_document_key') ?: null,
            ],
            'prerequisite_sacrament' => [
                'sacrament_type' => $request->get('param_sacrament_type') ?: null,
            ],
            'minimum_age' => [
                'min_age' => $request->get('param_min_age') ? (int)$request->get('param_min_age') : null,
            ],
            default => [],
        };
    }
}
