<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    use HasFactory;

    /**
     * Atribut yang boleh diisi secara massal.
     *
     * @var array
     */
    protected $fillable = [
        'ledger_entry_id',
        'item_name',
        'price',
        'quantity',
    ];
}
