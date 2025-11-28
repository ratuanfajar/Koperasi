<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LedgerEntry extends Model
{
    use HasFactory;

    /**
     * Atribut yang boleh diisi secara massal.
     *
     * @var array
     */
    protected $fillable = [
        'date',
        'account_code',
        'account_name',
        'debit',
        'credit',
        'description',
        'receipt_image_path',
        'transaction_group_id',
        'transaction_code'
    ];

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }
}
