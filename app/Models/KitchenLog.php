<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class KitchenLog extends Model
{
    protected $fillable = [
        'product_id',
        'batch_size',
        'times_cooked',
        'cooked_at',
    ];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}