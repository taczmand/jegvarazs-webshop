<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class TaxCategory extends Model
{
    use LogsActivity;

    protected $fillable = ['tax_name', 'tax_value', 'tax_description'];
}
