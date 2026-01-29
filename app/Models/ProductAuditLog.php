<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\User;
class ProductAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'product_name',
        'action',
        'old_price',
        'new_price',
    ];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}