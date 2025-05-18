<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxCategory extends Model
{
    protected $fillable = ['tax_name', 'tax_value', 'tax_description'];
}
