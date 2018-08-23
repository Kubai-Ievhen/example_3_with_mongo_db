<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Statement extends Eloquent
{
    protected $collection='vendor_summary';

    protected $primaryKey='_id';
}
