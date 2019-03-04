<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class MegaOdd extends Model{
    protected $table = 'mega_645_pos';

    protected $fillable = ['pattern', 'possibility'];
}