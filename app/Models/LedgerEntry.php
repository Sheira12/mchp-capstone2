<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LedgerEntry extends Model
{
    protected $fillable = [
        'type', 'category', 'description', 'amount',
        'entry_date', 'reference_number', 'notes', 'recorded_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'amount'     => 'decimal:2',
    ];

    // ── Credit categories (income) ─────────────────────────────
    const CREDIT_CATEGORIES = [
        'Donation'         => 'Donation',
        'Mass Stipend'     => 'Mass Stipend',
        'Baptism Fee'      => 'Baptism Fee',
        'Wedding Fee'      => 'Wedding Fee',
        'Burial Fee'       => 'Burial Fee',
        'Certificate Fee'  => 'Certificate Fee',
        'House Blessing'   => 'House Blessing Fee',
        'Seminar Fee'      => 'Seminar / Catechesis Fee',
        'Collection'       => 'Sunday/Special Collection',
        'Grant'            => 'Grant / Subsidy',
        'Other Income'     => 'Other Income',
    ];

    // ── Debit categories (expense) ─────────────────────────────
    const DEBIT_CATEGORIES = [
        'Utilities'        => 'Utilities (Electric/Water)',
        'Salary'           => 'Salary / Honorarium',
        'Maintenance'      => 'Maintenance & Repairs',
        'Office Supplies'  => 'Office Supplies',
        'Sacramentals'     => 'Sacramentals & Liturgical Items',
        'Events'           => 'Events & Programs',
        'Charitable'       => 'Charitable Activities',
        'Insurance'        => 'Insurance',
        'Bank Fees'        => 'Bank Charges & Fees',
        'Other Expense'    => 'Other Expenses',
    ];

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'credit' ? 'Income' : 'Expense';
    }
}
