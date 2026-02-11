<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'category',
        'price',
        'stock',
        'image',
    ];
    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }
    public function batchSizes()
    {
        return $this->hasMany(BatchSize::class);
    }
    public function kitchenLogs()
    {
        return $this->hasMany(KitchenLog::class);
    }
    public function ingredients()
    {
        return $this->belongsToMany(
            Ingredient::class,
            'recipes',
            'product_id',
            'ingredient_id'
        )
        ->withPivot(['quantity', 'batch_sizes_id'])
        ->withTimestamps();
    }
}