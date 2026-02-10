<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class KitchenLog extends Model
{
    protected $fillable = [
        'product_id',
        'batch_sizes_id',
        'times_cooked',
        'cooked_at',
    ];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function batchSize()
    {
        return $this->belongsTo(BatchSize::class, 'batch_sizes_id');
    }
}