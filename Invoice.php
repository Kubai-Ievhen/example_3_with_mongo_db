<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;


class Invoice extends Eloquent
{
    protected $collection='invoices';

    protected $primaryKey='_id';
}
