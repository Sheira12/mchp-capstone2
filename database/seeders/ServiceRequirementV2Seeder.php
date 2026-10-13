<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceRequirement;
use Illuminate\Database\Seeder;

/**
 * ServiceRequirementV2Seeder
 *
 * Seeds eligibility-gate requirements (eligibility_type = seminar | document |
 * sacrament_record) for services that need them.
 *
 * ⚠  ALL rows are PLACEHOLDERS — each parish has its own rules.
 *    Admin can edit/deactivate these at:
 *    /admin/services/{service}/requirements
 *
 * Services with no listed requirements (Car Blessing, Sick Call, etc.)
 * remain open — if no active eligibility-gate rows exist for a service,
 * BookingEligibilityService::check() returns eligible = true automatically.
 */
class ServiceRequirementV2Seeder extends Seeder
{
    private const NOTE = '⚠ PLACEHOLDER — confirm exact rules with your parish office before going live.';

    public function run(): void
    {
        $data = [

            // ─── Baptism ─────────────────────────────────────────────────────
            'baptism' => [
                [
                    'eligibility_type'       => 'seminar',
                    'name'                   => 'Pre-Baptismal Seminar (Parents & Godparents)',
                    'description'            => self::NOTE . ' At least one parent and one designated godparent must attend the Pre-Baptismal Seminar before the baby can be baptized.',
                    'required_sacrament_type'=> null,
                    'requires_proof'         => false,
                    'is_required'            => true,
                    'sort_order'             => 1,
                ],
                [
                    'eligibility_type'       => 'document',
                    'name'                   => 'Birth Certificate of Child',
                    'description'            => self::NOTE . ' PSA/NSO Birth Certificate (original or certified copy) must be presented.',
                    'required_sacrament_type'=> null,
                    'requires_proof'         => true,
                    'is_required'            => true,
                    'sort_order'             => 2,
                ],
            ],

            // ─── Confirmation Catechesis ─────────────────────────────────────
            'confirmation_catechesis' => [
                [
                    'eligibility_type'       => 'sacrament_record',
                    'name'                   => 'Baptismal Record (Candidate)',
                    'description'            => self::NOTE . ' Confirmation candidates must have been baptized. A baptismal certificate from another parish is accepted with declaration.',
                    'required_sacrament_type'=> 'baptism',
                    'requires_proof'         => true,
                    'is_required'            => true,
                    'sort_order'             => 1,
                ],
                [
                    'eligibility_type'       => 'seminar',
                    'name'                   => 'Confirmation Catechesis / Preparation',
                    'description'            => self::NOTE . ' Candidate must complete the Confirmation catechesis program.',
                    'required_sacrament_type'=> null,
                    'requires_proof'         => false,
                    'is_required'            => true,
                    'sort_order'             => 2,
                ],
            ],

            // ─── Wedding / Marriage ──────────────────────────────────────────
            'wedding' => [
                [
                    'eligibility_type'       => 'sacrament_record',
                    'name'                   => 'Baptismal Record — Applicant',
                    'description'            => self::NOTE . ' The primary applicant must have a baptismal record on file. A recent baptismal certificate (within 6 months) from another parish is accepted with declaration.',
                    'required_sacrament_type'=> 'baptism',
                    'requires_proof'         => true,
                    'is_required'            => true,
                    'sort_order'             => 1,
                ],
                [
                    'eligibility_type'       => 'sacrament_record',
                    'name'                   => 'Confirmation Record — Applicant',
                    'description'            => self::NOTE . ' Confirmation is generally required before marriage in the Catholic Church. Check with your parish priest.',
                    'required_sacrament_type'=> 'confirmation',
                    'requires_proof'         => true,
                    'is_required'            => false, // optional — mark required if your parish requires it
                    'sort_order'             => 2,
                ],
                [
                    'eligibility_type'       => 'seminar',
                    'name'                   => 'Pre-Marriage Seminar (Pre-Cana)',
                    'description'            => self::NOTE . ' Both the groom and bride must complete the Pre-Marriage (Pre-Cana) seminar. Must be completed at least 3 months before the wedding.',
                    'required_sacrament_type'=> null,
                    'requires_proof'         => false,
                    'is_required'            => true,
                    'sort_order'             => 3,
                ],
                [
                    'eligibility_type'       => 'document',
                    'name'                   => 'Civil Marriage License',
                    'description'            => self::NOTE . ' Civil Marriage License from the Local Civil Registrar (valid for 120 days from issuance).',
                    'required_sacrament_type'=> null,
                    'requires_proof'         => true,
                    'is_required'            => true,
                    'sort_order'             => 4,
                ],
                [
                    'eligibility_type'       => 'document',
                    'name'                   => 'Certificate of No Impediment',
                    'description'            => self::NOTE . ' Both parties must present a Certificate of No Impediment from their respective baptismal parishes.',
                    'required_sacrament_type'=> null,
                    'requires_proof'         => true,
                    'is_required'            => true,
                    'sort_order'             => 5,
                ],
            ],

            // ─── First Communion (via seminar booking) ───────────────────────
            'pre_baptismal' => [
                // Pre-Baptismal Seminar booking itself has no prerequisites —
                // it IS the prerequisite for Baptism. Leave open.
            ],

            'pre_marriage' => [
                // Pre-Marriage Seminar booking itself has no prerequisites —
                // it IS the prerequisite for Wedding. Leave open.
            ],

            // ─── Funeral Mass ────────────────────────────────────────────────
            'funeral_mass' => [
                [
                    'eligibility_type'       => 'document',
                    'name'                   => 'Death Certificate',
                    'description'            => self::NOTE . ' A death certificate (hospital or PSA) is required to schedule a Funeral Mass.',
                    'required_sacrament_type'=> null,
                    'requires_proof'         => true,
                    'is_required'            => true,
                    'sort_order'             => 1,
                ],
            ],

            // ─── Open services — intentionally no gate requirements ──────────
            // house_blessing, car_blessing, business_blessing, sick_call,
            // mass_intention, certificate — no rows = automatically eligible
        ];

        foreach ($data as $slug => $rows) {
            $service = Service::where('slug', $slug)->first();
            if (!$service || empty($rows)) continue;

            foreach ($rows as $row) {
                ServiceRequirement::firstOrCreate(
                    [
                        'service_id'       => $service->id,
                        'eligibility_type' => $row['eligibility_type'],
                        'name'             => $row['name'],
                    ],
                    array_merge($row, [
                        'service_id'   => $service->id,
                        'service_slug' => $slug,
                        'type'         => $row['eligibility_type'] === 'document' ? 'file' : 'text', // legacy compat
                        'is_active'    => true,
                    ])
                );
            }
        }

        // Back-fill service_slug on any existing rows that are missing it
        ServiceRequirement::whereNull('service_slug')
            ->orWhere('service_slug', '')
            ->get()
            ->each(function ($req) {
                if ($req->service_id) {
                    $slug = Service::find($req->service_id)?->slug;
                    if ($slug) $req->update(['service_slug' => $slug]);
                }
            });
    }
}
