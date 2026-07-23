<?php

namespace Database\Seeders;

use App\Models\LedgerEntry;
use Illuminate\Database\Seeder;

class LedgerSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing July 2026 entries to avoid duplicates
        LedgerEntry::whereBetween('entry_date', ['2026-07-01', '2026-07-31'])->delete();

        $entries = [
            // ── INCOME (Credit) ────────────────────────────────────────

            // Week 1
            ['credit', 'Collection',     'Sunday Collection — July 05, 2026',             12450.00, '2026-07-05', 'COL-2026-0705'],
            ['credit', 'Mass Stipend',   'Weekday Mass Stipends — Week 1',                 2200.00, '2026-07-07', 'MS-2026-0701'],
            ['credit', 'Donation',       'Anonymous Benefactor Donation',                  5000.00, '2026-07-02', 'DON-2026-0702'],
            ['credit', 'Baptism Fee',    'Baptism — Lim, Carlos Jr.',                      1500.00, '2026-07-03', 'BAP-2026-0703'],
            ['credit', 'Baptism Fee',    'Baptism — Santos, Maria Clara',                  1500.00, '2026-07-04', 'BAP-2026-0704'],

            // Week 2
            ['credit', 'Collection',     'Sunday Collection — July 12, 2026',             11800.00, '2026-07-12', 'COL-2026-0712'],
            ['credit', 'Mass Stipend',   'Weekday Mass Stipends — Week 2',                 1950.00, '2026-07-14', 'MS-2026-0702'],
            ['credit', 'Wedding Fee',    'Wedding — Reyes & Cruz',                         8000.00, '2026-07-11', 'WED-2026-0711'],
            ['credit', 'Certificate Fee','Certificate Fees — July Week 2',                 1800.00, '2026-07-13', 'CERT-2026-0713'],
            ['credit', 'Seminar Fee',    'Pre-Baptismal Seminar — 12 attendees',           1800.00, '2026-07-10', 'SEM-2026-0710'],

            // Week 3
            ['credit', 'Collection',     'Sunday Collection — July 19, 2026',             13200.00, '2026-07-19', 'COL-2026-0719'],
            ['credit', 'Mass Stipend',   'Weekday Mass Stipends — Week 3',                 2100.00, '2026-07-21', 'MS-2026-0703'],
            ['credit', 'House Blessing', 'House Blessing — Dela Cruz Family',              1500.00, '2026-07-17', 'HB-2026-0717'],
            ['credit', 'House Blessing', 'House Blessing — Villanueva Residence',          1500.00, '2026-07-18', 'HB-2026-0718'],
            ['credit', 'Donation',       'Parish Development Fund Donation',               3000.00, '2026-07-16', 'DON-2026-0716'],
            ['credit', 'Baptism Fee',    'Baptism — Garcia, Paulo Miguel',                 1500.00, '2026-07-19', 'BAP-2026-0719'],
            ['credit', 'Burial Fee',     'Burial Service — Mendoza, Rodrigo',              3500.00, '2026-07-20', 'BUR-2026-0720'],

            // Week 4
            ['credit', 'Collection',     'Sunday Collection — July 26, 2026',             12750.00, '2026-07-26', 'COL-2026-0726'],
            ['credit', 'Mass Stipend',   'Weekday Mass Stipends — Week 4',                 2050.00, '2026-07-28', 'MS-2026-0704'],
            ['credit', 'Certificate Fee','Certificate Fees — July Week 4',                 2250.00, '2026-07-25', 'CERT-2026-0725'],
            ['credit', 'Grant',          'Diocese Parish Support Fund — July 2026',        5000.00, '2026-07-23', 'GR-2026-0723'],
            ['credit', 'Other Income',   'Candle & Religious Items Sales',                  850.00, '2026-07-22', 'OTH-2026-0722'],

            // ── EXPENSES (Debit) ───────────────────────────────────────

            // Utilities
            ['debit',  'Utilities',      'MERALCO Electric Bill — July 2026',              8750.00, '2026-07-08', 'UTIL-2026-0708'],
            ['debit',  'Utilities',      'Maynilad Water Bill — July 2026',                1250.00, '2026-07-08', 'UTIL-2026-0708B'],

            // Salaries
            ['debit',  'Salary',         'Parish Secretary Honorarium — July 2026',        8000.00, '2026-07-15', 'SAL-2026-0715'],
            ['debit',  'Salary',         'Sacristan Honorarium — July 2026',               4500.00, '2026-07-15', 'SAL-2026-0715B'],
            ['debit',  'Salary',         'Maintenance Staff Salary — July 2026',           6000.00, '2026-07-15', 'SAL-2026-0715C'],

            // Maintenance
            ['debit',  'Maintenance',    'Repair of PA System & Microphones',              3200.00, '2026-07-06', 'MAINT-2026-0706'],
            ['debit',  'Maintenance',    'Pest Control Service — Church Premises',         1500.00, '2026-07-09', 'MAINT-2026-0709'],
            ['debit',  'Maintenance',    'Garden & Grounds Maintenance',                    800.00, '2026-07-22', 'MAINT-2026-0722'],

            // Sacramentals
            ['debit',  'Sacramentals',   'Candles, Holy Water & Sacramental Supplies',     2850.00, '2026-07-07', 'SAC-2026-0707'],
            ['debit',  'Sacramentals',   'Liturgical Vestments Cleaning & Repair',          950.00, '2026-07-14', 'SAC-2026-0714'],

            // Office Supplies
            ['debit',  'Office Supplies','Office Supplies — July 2026 (Paper, Ink, etc.)', 1450.00, '2026-07-03', 'OFFC-2026-0703'],
            ['debit',  'Office Supplies','Printer Cartridge Replacement',                   750.00, '2026-07-17', 'OFFC-2026-0717'],

            // Events
            ['debit',  'Events',         'Feast Day of St. Camillus Celebration',          4500.00, '2026-07-18', 'EVT-2026-0718'],
            ['debit',  'Events',         'Youth Ministry Monthly Activity',                1800.00, '2026-07-24', 'EVT-2026-0724'],

            // Charitable
            ['debit',  'Charitable',     'Financial Assistance — Parishioner in Need',     2000.00, '2026-07-10', 'CHA-2026-0710'],
            ['debit',  'Charitable',     'Barangay Outreach Program Supplies',             3500.00, '2026-07-21', 'CHA-2026-0721'],

            // Bank & Insurance
            ['debit',  'Bank Fees',      'Bank Service Charges — July 2026',                350.00, '2026-07-01', 'BANK-2026-0701'],
            ['debit',  'Insurance',      'Parish Property Insurance — Monthly Premium',    2200.00, '2026-07-01', 'INS-2026-0701'],
        ];

        $adminId = \DB::table('users')->where('email', 'maryhelpparish@gmail.com')->value('id') ?? 1;

        $count = 0;
        foreach ($entries as [$type, $category, $description, $amount, $date, $ref]) {
            LedgerEntry::create([
                'type'             => $type,
                'category'         => $category,
                'description'      => $description,
                'amount'           => $amount,
                'entry_date'       => $date,
                'reference_number' => $ref,
                'recorded_by'      => $adminId,
            ]);
            $count++;
        }

        $totalCredit = collect($entries)->where(0, 'credit')->sum(3);
        $totalDebit  = collect($entries)->where(0, 'debit')->sum(3);

        $this->command->info("Ledger seeded: {$count} entries for July 2026");
        $this->command->info('  Total Income  : ₱' . number_format($totalCredit, 2));
        $this->command->info('  Total Expenses: ₱' . number_format($totalDebit, 2));
        $this->command->info('  Net Balance   : ₱' . number_format($totalCredit - $totalDebit, 2));
    }
}
