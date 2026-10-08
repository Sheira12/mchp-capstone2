<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServicePackage;
use Illuminate\Database\Seeder;

class ServicePackageSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'wedding' => [
                [
                    'name'        => 'Standard Package',
                    'description' => 'Basic wedding ceremony with essential decorations.',
                    'inclusions'  => [
                        'Church ceremony (90 minutes)',
                        'Basic floral arrangement at altar',
                        '2 candelabras',
                        'Aisle runner',
                        'Sound system',
                    ],
                    'price'      => 5000.00,
                    'sort_order' => 1,
                ],
                [
                    'name'        => 'Classic Package',
                    'description' => 'Full ceremony with enhanced décor and additional services.',
                    'inclusions'  => [
                        'Church ceremony (90 minutes)',
                        'Enhanced floral arrangement (altar + pews)',
                        '4 candelabras',
                        'Aisle runner',
                        'Sound system',
                        'Wedding coordinator (day-of)',
                        'Certificate preparation',
                    ],
                    'price'      => 10000.00,
                    'sort_order' => 2,
                ],
                [
                    'name'        => 'Premium Package',
                    'description' => 'Full-service wedding with premium floral, music, and coordination.',
                    'inclusions'  => [
                        'Church ceremony (90 minutes)',
                        'Premium floral arrangement (altar + full pew décor)',
                        '6 candelabras with chandeliers',
                        'Red carpet aisle runner',
                        'Professional sound system + choir',
                        'Full-day wedding coordinator',
                        'Certificate preparation',
                        'Bride & groom souvenir candles',
                        'Post-ceremony blessing card',
                    ],
                    'price'      => 20000.00,
                    'sort_order' => 3,
                ],
            ],

            'baptism' => [
                [
                    'name'        => 'Simple Baptism',
                    'description' => 'Basic baptismal ceremony.',
                    'inclusions'  => [
                        'Baptismal ceremony (60 minutes)',
                        'Baptismal certificate',
                        '1 baptismal candle',
                    ],
                    'price'      => 500.00,
                    'sort_order' => 1,
                ],
                [
                    'name'        => 'Solemn Baptism Package',
                    'description' => 'Full baptismal ceremony with decorations and mementos.',
                    'inclusions'  => [
                        'Baptismal ceremony (60 minutes)',
                        'Baptismal certificate',
                        'Floral arrangement at baptismal font',
                        '2 decorated baptismal candles',
                        'Baptismal souvenir card',
                        'Professional sound system',
                    ],
                    'price'      => 1500.00,
                    'sort_order' => 2,
                ],
            ],

            'funeral_mass' => [
                [
                    'name'        => 'Standard Funeral Mass',
                    'description' => 'Standard funeral Mass with basic services.',
                    'inclusions'  => [
                        'Funeral Mass (60 minutes)',
                        'Sound system',
                        '2 candelabras',
                        'Death certificate preparation assistance',
                    ],
                    'price'      => 1500.00,
                    'sort_order' => 1,
                ],
                [
                    'name'        => 'Solemn Funeral Mass',
                    'description' => 'Solemn Mass with floral tributes and choir.',
                    'inclusions'  => [
                        'Funeral Mass (90 minutes)',
                        'Floral arrangement',
                        '4 candelabras',
                        'Parish choir',
                        'Sound system',
                        'Death certificate preparation assistance',
                        'Condolence card',
                    ],
                    'price'      => 3500.00,
                    'sort_order' => 2,
                ],
            ],

            'confirmation_catechesis' => [
                [
                    'name'        => 'Confirmation Catechesis',
                    'description' => 'Standard confirmation preparation program.',
                    'inclusions'  => [
                        'Full catechesis program (multiple sessions)',
                        'Workbook / module',
                        'Confirmation certificate upon completion',
                    ],
                    'price'      => 200.00,
                    'sort_order' => 1,
                ],
            ],

            'house_blessing' => [
                [
                    'name'        => 'House Blessing',
                    'description' => 'Standard house blessing by parish priest.',
                    'inclusions'  => [
                        'House blessing ceremony',
                        'Holy water and prayers',
                    ],
                    'price'      => 300.00,
                    'sort_order' => 1,
                ],
            ],

            'pre_marriage' => [
                [
                    'name'        => 'Pre-Cana Seminar',
                    'description' => 'Required pre-marriage seminar for engaged couples.',
                    'inclusions'  => [
                        'Full-day seminar for both parties',
                        'Seminar materials / workbook',
                        'Pre-Cana certificate upon completion',
                        'Snacks/refreshments',
                    ],
                    'price'      => 500.00,
                    'sort_order' => 1,
                ],
            ],
        ];

        foreach ($data as $slug => $packages) {
            $service = Service::where('slug', $slug)->first();
            if (!$service) continue;

            foreach ($packages as $pkg) {
                ServicePackage::firstOrCreate(
                    [
                        'service_id' => $service->id,
                        'name'       => $pkg['name'],
                    ],
                    array_merge($pkg, ['service_id' => $service->id, 'is_active' => true])
                );
            }
        }
    }
}
