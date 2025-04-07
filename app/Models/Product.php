<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable=['product_name','user_id','product_image','brand','warranty','price','capacity','quantity','descriptions'];
}
