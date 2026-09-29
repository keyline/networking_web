<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use App\Traits\JsonDecodeTrait;

class ExtendedCollection extends Collection
{
    use JsonDecodeTrait;
}
