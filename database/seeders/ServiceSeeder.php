<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            // Sacraments
            [
                'name'             => 'Baptism',
                'slug'             => 'baptism',
                'category'         => 'Sacraments',
                'description'      => 'The sacrament of initiation into the Christian faith.',
                'requirements'     => ['Birth Certificate', 'Pre-Baptismal Seminar Certificate', 'Godparents must be confirmed Catholics'],
                'fee'              => 500.00,
                'duration_minutes' => 60,
                'is_bookable'      => true,
                'sort_order'       => 1,
            ],
            [
                'name'             => 'Wedding / Marriage',
                'slug'             => 'wedding',
                'category'         => 'Sacraments',
                'description'      => 'The sacrament of matrimony.',
                'requirements'     => ['Baptismal Certificate (within 6 months)', 'Confirmation Certificate', 'Pre-Marriage Seminar Certificate', 'Certificate of No Impediment', 'Civil Marriage Certificate'],
                'fee'              => 3000.00,
                'duration_minutes' => 90,
                'is_bookable'      => true,
                'sort_order'       => 2,
            ],
            [
                'name'             => 'Funeral Mass',
                'slug'             => 'funeral_mass',
                'category'         => 'Sacraments',
                'description'      => 'Mass for the deceased.',
                'requirements'     => ['Death Certificate'],
                'fee'              => 1500.00,
                'duration_minutes' => 60,
                'is_bookable'      => true,
                'sort_order'       => 3,
            ],
            // Sacramentals
            [
                'name'             => 'House Blessing',
                'slug'             => 'house_blessing',
                'category'         => 'Sacramentals',
                'description'      => 'Blessing of a new or existing home.',
                'requirements'     => [],
                'fee'              => 300.00,
                'duration_minutes' => 30,
                'is_bookable'      => true,
                'sort_order'       => 10,
            ],
            [
                'name'             => 'Car Blessing',
                'slug'             => 'car_blessing',
                'category'         => 'Sacramentals',
                'description'      => 'Blessing of a vehicle.',
                'requirements'     => [],
                'fee'              => 200.00,
                'duration_minutes' => 15,
                'is_bookable'      => true,
                'sort_order'       => 11,
            ],
            [
                'name'             => 'Business Blessing',
                'slug'             => 'business_blessing',
                'category'         => 'Sacramentals',
                'description'      => 'Blessing of a business establishment.',
                'requirements'     => [],
                'fee'              => 300.00,
                'duration_minutes' => 30,
                'is_bookable'      => true,
                'sort_order'       => 12,
            ],
            [
                'name'             => 'Sick Call / Anointing of the Sick',
                'slug'             => 'sick_call',
                'category'         => 'Sacramentals',
                'description'      => 'Anointing of the sick and dying.',
                'requirements'     => [],
                'fee'              => 0.00,
                'duration_minutes' => 30,
                'is_bookable'      => true,
                'sort_order'       => 13,
            ],
            // Seminars
            [
                'name'             => 'Pre-Baptismal Seminar',
                'slug'             => 'pre_baptismal',
                'category'         => 'Seminars',
                'description'      => 'Required seminar for parents and godparents before baptism.',
                'requirements'     => ['Parents and at least one godparent must attend'],
                'fee'              => 100.00,
                'duration_minutes' => 180,
                'is_bookable'      => true,
                'sort_order'       => 20,
            ],
            [
                'name'             => 'Pre-Marriage Seminar (Pre-Cana)',
                'slug'             => 'pre_marriage',
                'category'         => 'Seminars',
                'description'      => 'Required seminar for couples planning to marry in the Church.',
                'requirements'     => ['Both parties must attend', 'Must be completed at least 3 months before wedding'],
                'fee'              => 500.00,
                'duration_minutes' => 480,
                'is_bookable'      => true,
                'sort_order'       => 21,
            ],
            [
                'name'             => 'Confirmation Catechesis',
                'slug'             => 'confirmation_catechesis',
                'category'         => 'Seminars',
                'description'      => 'Catechesis program for confirmation candidates.',
                'requirements'     => ['Baptismal Certificate', 'First Communion Certificate'],
                'fee'              => 200.00,
                'duration_minutes' => 240,
                'is_bookable'      => true,
                'sort_order'       => 22,
            ],
            // Mass Intentions
            [
                'name'             => 'Mass Intention',
                'slug'             => 'mass_intention',
                'category'         => 'Mass',
                'description'      => 'Request a Mass to be offered for a specific intention.',
                'requirements'     => [],
                'fee'              => 200.00,
                'duration_minutes' => 0,
                'is_bookable'      => true,
                'sort_order'       => 30,
            ],
            // Certificates
            [
                'name'             => 'Parish Certificate',
                'slug'             => 'certificate',
                'category'         => 'Certificates',
                'description'      => 'Request a copy of a parish certificate (Baptism, Confirmation, Marriage, etc.).',
                'requirements'     => ['Valid ID'],
                'fee'              => 100.00,
                'duration_minutes' => 0,
                'is_bookable'      => false,
                'sort_order'       => 40,
            ],
        ];

        foreach ($services as $service) {
            Service::firstOrCreate(['slug' => $service['slug']], $service);
        }
    }
}
