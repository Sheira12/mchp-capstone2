<?php

namespace Database\Seeders;

use App\Models\EligibilityRule;
use App\Models\Service;
use Illuminate\Database\Seeder;

/**
 * EligibilityRuleSeeder
 *
 * Seeds placeholder eligibility rules for all services.
 *
 * ⚠  ALL RULES ARE MARKED is_placeholder = true.
 * ⚠  The parish office MUST review and confirm/update these
 * ⚠  before going live. Admin can edit them at
 * ⚠  /admin/services/{service}/eligibility-rules
 */
class EligibilityRuleSeeder extends Seeder
{
    public function run(): void
    {
        $placeholder = '⚠ PLACEHOLDER — confirm with parish office before going live.';

        $rules = [
            // ─── Baptism ────────────────────────────────────────────────────
            'baptism' => [
                [
                    'criterion_type' => 'seminar_completed',
                    'applies_to'     => 'parents',
                    'params'         => ['service_slug' => 'pre_baptismal', 'validity_days' => null],
                    'name'           => 'Pre-Baptismal Seminar (Parents)',
                    'description'    => "$placeholder At least one parent must have attended the Pre-Baptismal Seminar.",
                    'is_required'    => true,
                    'sort_order'     => 1,
                ],
                [
                    'criterion_type' => 'seminar_completed',
                    'applies_to'     => 'godparents',
                    'params'         => ['service_slug' => 'pre_baptismal', 'validity_days' => null],
                    'name'           => 'Pre-Baptismal Seminar (Godparents)',
                    'description'    => "$placeholder At least one designated godparent must have attended the Pre-Baptismal Seminar.",
                    'is_required'    => true,
                    'sort_order'     => 2,
                ],
                [
                    'criterion_type' => 'parishioner_status',
                    'applies_to'     => 'applicant',
                    'params'         => [],
                    'name'           => 'Active Parishioner Profile',
                    'description'    => "$placeholder The applicant must have an active parishioner profile in the system.",
                    'is_required'    => true,
                    'sort_order'     => 3,
                ],
            ],

            // ─── Wedding ────────────────────────────────────────────────────
            'wedding' => [
                [
                    'criterion_type' => 'seminar_completed',
                    'applies_to'     => 'both',
                    'params'         => ['service_slug' => 'pre_marriage', 'validity_days' => null],
                    'name'           => 'Pre-Marriage Seminar (Pre-Cana)',
                    'description'    => "$placeholder Both the groom and bride must have completed the Pre-Marriage (Pre-Cana) seminar.",
                    'is_required'    => true,
                    'sort_order'     => 1,
                ],
                [
                    'criterion_type' => 'prerequisite_sacrament',
                    'applies_to'     => 'applicant',
                    'params'         => ['sacrament_type' => 'baptism'],
                    'name'           => 'Baptismal Record (Applicant)',
                    'description'    => "$placeholder The applicant must have a Baptism record on file.",
                    'is_required'    => true,
                    'sort_order'     => 2,
                ],
                [
                    'criterion_type' => 'prerequisite_sacrament',
                    'applies_to'     => 'spouse',
                    'params'         => ['sacrament_type' => 'baptism'],
                    'name'           => 'Baptismal Record (Spouse)',
                    'description'    => "$placeholder The spouse must also have a Baptism record. Admin verifies manually for now.",
                    'is_required'    => true,
                    'sort_order'     => 3,
                ],
                [
                    'criterion_type' => 'prerequisite_sacrament',
                    'applies_to'     => 'applicant',
                    'params'         => ['sacrament_type' => 'confirmation'],
                    'name'           => 'Confirmation Record (Applicant)',
                    'description'    => "$placeholder Confirmation is generally required before marriage. Confirm with parish priest.",
                    'is_required'    => false,
                    'sort_order'     => 4,
                ],
                [
                    'criterion_type' => 'document_approved',
                    'applies_to'     => 'applicant',
                    'params'         => ['document_key' => 'Certificate of No Impediment'],
                    'name'           => 'Certificate of No Impediment',
                    'description'    => "$placeholder Both parties need a Certificate of No Impediment from their baptismal parish.",
                    'is_required'    => true,
                    'sort_order'     => 5,
                ],
                [
                    'criterion_type' => 'document_approved',
                    'applies_to'     => 'applicant',
                    'params'         => ['document_key' => 'Civil Marriage License'],
                    'name'           => 'Civil Marriage License',
                    'description'    => "$placeholder Civil Marriage License from the Local Civil Registrar (valid 120 days).",
                    'is_required'    => true,
                    'sort_order'     => 6,
                ],
            ],

            // ─── Confirmation (Catechesis booking) ──────────────────────────
            'confirmation_catechesis' => [
                [
                    'criterion_type' => 'prerequisite_sacrament',
                    'applies_to'     => 'applicant',
                    'params'         => ['sacrament_type' => 'baptism'],
                    'name'           => 'Baptismal Record',
                    'description'    => "$placeholder Candidate must have a Baptism record before attending Confirmation catechesis.",
                    'is_required'    => true,
                    'sort_order'     => 1,
                ],
                [
                    'criterion_type' => 'prerequisite_sacrament',
                    'applies_to'     => 'applicant',
                    'params'         => ['sacrament_type' => 'first_communion'],
                    'name'           => 'First Communion Record',
                    'description'    => "$placeholder Candidate should have received First Communion. Confirm with parish catechist.",
                    'is_required'    => false,
                    'sort_order'     => 2,
                ],
                [
                    'criterion_type' => 'minimum_age',
                    'applies_to'     => 'applicant',
                    'params'         => ['min_age' => 13],
                    'name'           => 'Minimum Age (13)',
                    'description'    => "$placeholder Confirmation candidates are typically at least 13 years old. Verify with parish.",
                    'is_required'    => false,
                    'sort_order'     => 3,
                ],
            ],

            // ─── Funeral Mass ───────────────────────────────────────────────
            'funeral_mass' => [
                [
                    'criterion_type' => 'parishioner_status',
                    'applies_to'     => 'applicant',
                    'params'         => [],
                    'name'           => 'Active Parishioner Profile',
                    'description'    => "$placeholder The person requesting the funeral mass must have an active profile.",
                    'is_required'    => true,
                    'sort_order'     => 1,
                ],
            ],

            // ─── Pre-Baptismal Seminar ───────────────────────────────────────
            'pre_baptismal' => [
                [
                    'criterion_type' => 'parishioner_status',
                    'applies_to'     => 'applicant',
                    'params'         => [],
                    'name'           => 'Active Parishioner Profile',
                    'description'    => "$placeholder Applicant must be registered as a parishioner.",
                    'is_required'    => true,
                    'sort_order'     => 1,
                ],
            ],

            // ─── Pre-Marriage Seminar ────────────────────────────────────────
            'pre_marriage' => [
                [
                    'criterion_type' => 'parishioner_status',
                    'applies_to'     => 'applicant',
                    'params'         => [],
                    'name'           => 'Active Parishioner Profile',
                    'description'    => "$placeholder Applicant must be registered as a parishioner.",
                    'is_required'    => true,
                    'sort_order'     => 1,
                ],
            ],

            // ─── House / Car / Business Blessing — intentionally open ────────
            'house_blessing'    => [], // No eligibility rules — open service
            'car_blessing'      => [],
            'business_blessing' => [],

            // ─── Sick Call — open service ────────────────────────────────────
            'sick_call' => [],

            // ─── Mass Intention — open service ──────────────────────────────
            'mass_intention' => [],
        ];

        foreach ($rules as $slug => $serviceRules) {
            $service = Service::where('slug', $slug)->first();
            if (!$service || empty($serviceRules)) continue;

            foreach ($serviceRules as $rule) {
                EligibilityRule::firstOrCreate(
                    [
                        'service_id'     => $service->id,
                        'criterion_type' => $rule['criterion_type'],
                        'name'           => $rule['name'],
                    ],
                    array_merge($rule, [
                        'service_id'     => $service->id,
                        'is_active'      => true,
                        'is_placeholder' => true,
                    ])
                );
            }
        }
    }
}
