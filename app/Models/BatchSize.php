<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class BatchSize extends Model
{
    // unsay naas table 
    protected $fillable = [
        'product_id',
        'servings',
    ];
    // connection to product since gina kuha man natong product id
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    // connection to recipe. one batch to multiple recipes
    public function recipes()
    {
        return $this->hasMany(Recipe::class, 'batch_sizes_id');
    }
    // connection to kitchen logs. one batch has many kitchen logs
    public function kitchenLogs()
    {
        return $this->hasMany(KitchenLog::class, 'batch_sizes_id');
    }
}