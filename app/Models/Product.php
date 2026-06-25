<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes; // Add this trait

    // Ensure status is included in your fillable array if you use one
    protected $fillable = ['name', 'sku', 'price', 'status', 'image'];
}
