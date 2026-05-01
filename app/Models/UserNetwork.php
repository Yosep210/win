<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'sponsor_id',
    'parent_id',
    'position',
    'generation',
    'level',
    'group',
    'user_hu_id',
    'tree',
    'tree_sponsor',
])]
class UserNetwork extends Model
{
    use SoftDeletes;
}
