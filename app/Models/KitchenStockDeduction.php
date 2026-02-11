<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class KitchenStockDeduction extends Model
{
    protected $fillable = [
        'kitchen_production_log_id',
        'ingredient_id',
        'ingredient_name',
        'quantity_deducted',
        'unit',
    ];

    public function productionLog()
    {
        return $this->belongsTo(KitchenProductionLog::class, 'kitchen_production_log_id');
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
