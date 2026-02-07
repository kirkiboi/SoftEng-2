<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Recipe;
use App\Models\Ingredient;
class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'category',
        'price',
        'image',
    ];
    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }
    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'recipes')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }
}