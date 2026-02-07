<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Recipe;
use App\Models\Product;
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
        return $this->belongsToMany(Product::class, 'recipes')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }
}
