<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceRequirement;
use Illuminate\Database\Seeder;

class ServiceRequirementSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // ── Baptism ──────────────────────────────────────────────────────────
            'baptism' => [
                ['name' => 'Birth Certificate of Child', 'description' => 'Original or certified true copy of the child\'s PSA/NSO Birth Certificate.', 'type' => 'file', 'is_required' => true, 'accepted_file_types' => 'pdf,jpg,jpeg,png', 'sort_order' => 1],
                ['name' => 'Pre-Baptismal Seminar Certificate', 'description' => 'Certificate of completion for the Pre-Baptismal Seminar attended by at least one parent and one godparent.', 'type' => 'file', 'is_required' => true, 'accepted_file_types' => 'pdf,jpg,jpeg,png', 'sort_order' => 2],
                ['name' => 'Godparents are Confirmed Catholics', 'description' => 'I confirm that the chosen godparents are confirmed Catholics in good standing.', 'type' => 'checkbox', 'is_required' => true, 'accepted_file_types' => null, 'sort_order' => 3],
                ['name' => 'Marriage Certificate of Parents (if married in Church)', 'description' => 'Church/civil marriage certificate of the child\'s parents, if applicable.', 'type' => 'file', 'is_required' => false, 'accepted_file_types' => 'pdf,jpg,jpeg,png', 'sort_order' => 4],
            ],

            // ── Wedding ──────────────────────────────────────────────────────────
            'wedding' => [
                ['name' => 'Baptismal Certificate — Groom (within 6 months)', 'description' => 'Recent Baptismal Certificate of the groom issued within the last 6 months.', 'type' => 'file', 'is_required' => true, 'accepted_file_types' => 'pdf,jpg,jpeg,png', 'sort_order' => 1],
                ['name' => 'Baptismal Certificate — Bride (within 6 months)', 'description' => 'Recent Baptismal Certificate of the bride issued within the last 6 months.', 'type' => 'file', 'is_required' => true, 'accepted_file_types' => 'pdf,jpg,jpeg,png', 'sort_order' => 2],
                ['name' => 'Confirmation Certificate — Groom', 'description' => 'Certificate of Confirmation of the groom.', 'type' => 'file', 'is_required' => true, 'accepted_file_types' => 'pdf,jpg,jpeg,png', 'sort_order' => 3],
                ['name' => 'Confirmation Certificate — Bride', 'description' => 'Certificate of Confirmation of the bride.', 'type' => 'file', 'is_required' => true, 'accepted_file_types' => 'pdf,jpg,jpeg,png', 'sort_order' => 4],
                ['name' => 'Pre-Marriage Seminar (Pre-Cana) Certificate', 'description' => 'Certificate of completion of the Pre-Marriage / Pre-Cana Seminar by both parties.', 'type' => 'file', 'is_required' => true, 'accepted_file_types' => 'pdf,jpg,jpeg,png', 'sort_order' => 5],
                ['name' => 'Certificate of No Impediment', 'description' => 'Issued by the parish where each party was baptized; states no canonical impediments to marriage.', 'type' => 'file', 'is_required' => true, 'accepted_file_types' => 'pdf,jpg,jpeg,png', 'sort_order' => 6],
                ['name' => 'Civil Marriage License', 'description' => 'Civil Marriage License from the Local Civil Registrar (valid for 120 days).', 'type' => 'file', 'is_required' => true, 'accepted_file_types' => 'pdf,jpg,jpeg,png', 'sort_order' => 7],
                ['name' => 'PSA Birth Certificate — Groom', 'description' => 'PSA-authenticated birth certificate of the groom.', 'type' => 'file', 'is_required' => false, 'accepted_file_types' => 'pdf,jpg,jpeg,png', 'sort_order' => 8],
                ['name' => 'PSA Birth Certificate — Bride', 'description' => 'PSA-authenticated birth certificate of the bride.', 'type' => 'file', 'is_required' => false, 'accepted_file_types' => 'pdf,jpg,jpeg,png', 'sort_order' => 9],
                ['name' => 'Sponsor List (Ninongs & Ninangs)', 'description' => 'Full list of principal sponsors with complete names.', 'type' => 'text', 'is_required' => false, 'accepted_file_types' => null, 'sort_order' => 10],
            ],

            // ── Funeral Mass ─────────────────────────────────────────────────────
            'funeral_mass' => [
                ['name' => 'Death Certificate', 'description' => 'PSA/NSO Death Certificate or hospital certificate of death.', 'type' => 'file', 'is_required' => true, 'accepted_file_types' => 'pdf,jpg,jpeg,png', 'sort_order' => 1],
                ['name' => 'Name and age of the deceased', 'description' => 'Please enter the full name and age of the deceased.', 'type' => 'text', 'is_required' => true, 'accepted_file_types' => null, 'sort_order' => 2],
            ],

            // ── Pre-Baptismal Seminar ─────────────────────────────────────────────
            'pre_baptismal' => [
                ['name' => 'Both parents (or at least one) and one godparent will attend', 'description' => 'I confirm that at least one parent and one designated godparent will be present at the seminar.', 'type' => 'checkbox', 'is_required' => true, 'accepted_file_types' => null, 'sort_order' => 1],
                ['name' => 'Name of the child to be baptized', 'description' => 'Enter the full name of the child.', 'type' => 'text', 'is_required' => true, 'accepted_file_types' => null, 'sort_order' => 2],
            ],

            // ── Pre-Marriage Seminar ──────────────────────────────────────────────
            'pre_marriage' => [
                ['name' => 'Both parties will attend the full seminar', 'description' => 'I confirm that both the groom and bride will attend the complete Pre-Marriage / Pre-Cana seminar.', 'type' => 'checkbox', 'is_required' => true, 'accepted_file_types' => null, 'sort_order' => 1],
                ['name' => 'Intended Wedding Date', 'description' => 'Enter your intended church wedding date (must be at least 3 months from seminar).', 'type' => 'text', 'is_required' => true, 'accepted_file_types' => null, 'sort_order' => 2],
            ],

            // ── Confirmation Catechesis ───────────────────────────────────────────
            'confirmation_catechesis' => [
                ['name' => 'Baptismal Certificate', 'description' => 'PSA or parish-issued Baptismal Certificate.', 'type' => 'file', 'is_required' => true, 'accepted_file_types' => 'pdf,jpg,jpeg,png', 'sort_order' => 1],
                ['name' => 'First Communion Certificate', 'description' => 'Certificate of First Communion.', 'type' => 'file', 'is_required' => true, 'accepted_file_types' => 'pdf,jpg,jpeg,png', 'sort_order' => 2],
                ['name' => 'Valid School ID or Birth Certificate', 'description' => 'Proof of identity/age for the candidate.', 'type' => 'file', 'is_required' => false, 'accepted_file_types' => 'pdf,jpg,jpeg,png', 'sort_order' => 3],
            ],

            // ── House / Car / Business Blessing — no required documents ───────────
            'house_blessing'    => [],
            'car_blessing'      => [],
            'business_blessing' => [],
            'sick_call'         => [],
            'mass_intention'    => [],
        ];

        foreach ($data as $slug => $requirements) {
            $service = Service::where('slug', $slug)->first();
            if (!$service) continue;

            foreach ($requirements as $req) {
                ServiceRequirement::firstOrCreate(
                    [
                        'service_id' => $service->id,
                        'name'       => $req['name'],
                    ],
                    array_merge($req, ['service_id' => $service->id])
                );
            }
        }
    }
}
