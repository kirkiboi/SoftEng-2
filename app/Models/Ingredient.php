<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Ingredient extends Model
{
    protected $fillable = [
        'name',
        'category',
        'unit',
        'cost_per_unit',
        'stock',
        'threshold'
    ];
}