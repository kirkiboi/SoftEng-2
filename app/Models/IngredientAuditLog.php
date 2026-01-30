<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class IngredientAuditLog extends Model
{   
    protected $fillable = [
        'user_id',
        'ingredient_id',
        'unit_cost',
        'total_cost',
    ];
    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}