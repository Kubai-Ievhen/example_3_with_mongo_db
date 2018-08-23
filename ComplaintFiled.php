<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;


class ComplaintFiled extends Eloquent
{
    protected $collection='partner_complaints';

    protected $primaryKey='_id';
}
