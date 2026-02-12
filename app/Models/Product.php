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
        'image',
    ];
    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }
    public function kitchenLogs()
    {
        return $this->hasMany(KitchenLog::class);
    }
    public function auditLogs()
    {
        return $this->hasMany(ProductAuditLog::class);
    }
}