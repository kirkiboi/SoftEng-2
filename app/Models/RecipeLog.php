<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RecipeLog extends Model
{
    protected $table = 'recipe_audit_logs';
    protected $fillable = [
        'user_id',
        'recipe_id',
        'old_quantity',
        'new_quantity',
        'action',
    ];
    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}