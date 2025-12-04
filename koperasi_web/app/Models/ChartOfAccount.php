<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    public function entries()
    {
        return $this->hasMany(LedgerEntry::class, 'account_code', 'code');
    }
}
