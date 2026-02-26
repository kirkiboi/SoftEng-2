<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Ingredient;
use App\Models\User;

class IngredientAuditLog extends Model
{   
    protected $fillable = [
        'user_id',
        'ingredient_id',
        'ingredient_name',
        'unit_cost',
        'total_cost',
        'action',
        'quantity_changed',
        'old_stock',
        'new_stock',
        'supplier',
        'old_values',
        'new_values',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];
    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}