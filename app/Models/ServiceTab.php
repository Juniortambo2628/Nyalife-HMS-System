<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceTab extends Model
{
    protected $fillable = [
        'title',
        'icon',
        'content_title',
        'content_lead',
        'content_body',
        'image_path',
        'sort_order',
    ];
}
