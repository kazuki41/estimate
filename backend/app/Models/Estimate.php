<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estimate extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'title', 'total_amount'];

    // 💡 「1つの方の見積もりは、複数の明細（Details）を持つ」という関係を定義
    public function details()
    {
        return $this->hasMany(EstimateDetail::class);
    }
}