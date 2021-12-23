<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    public function wallet() {
        return $this->hasOne(Wallet::class, 'currency_id', 'id');
    }

    public function account() {
        return $this->hasMany(Account::class, 'currency_id', 'id');
    }
}
