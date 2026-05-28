<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstimateItem extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $fillable = [
        'name',
        'category',
        'price',
        'description',
    ];
}
