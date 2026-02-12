<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class KitchenProductionLog extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'product_name',
        'batch_size',
        'times_cooked',
        'total_servings',
        'status',
        'waste_reason',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function deductions()
    {
        return $this->hasMany(KitchenStockDeduction::class);
    }
}
