<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Order extends Model
{
    use HasFactory; 

     use Notifiable;

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected $table = 'orders';

    protected $fillable = [ 'name', 
                            'email', 
                            'phone', 
                            'address', 
                            'user_id', 

                            'product_title', 
                            'quantity',
                            'price', 
                            'image', 
                            'Product_id',

                            'payment_status',
                            'delivery_status'

                        ];
                            
}
