<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Trip extends Eloquent
{
    protected $collection='trips';

    protected $primaryKey='_id';
}
