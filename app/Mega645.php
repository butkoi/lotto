<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Mega645 extends Model{
    protected $table = 'mega_645_s';
    protected $fillable = [
        'date_roll',
        'jackpot_numbers'
    ];
}