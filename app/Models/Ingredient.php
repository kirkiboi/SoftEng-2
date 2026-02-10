<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Ingredient extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'category',
        'unit',
        'cost_per_unit',
        'stock',
        'threshold',
    ];
    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }
    public function products()
    {
        return $this->belongsToMany(
            Product::class,
            'recipes',
            'ingredient_id',
            'product_id'
        )
        ->withPivot(['quantity', 'batch_sizes_id'])
        ->withTimestamps();
    }
}