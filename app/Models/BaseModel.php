<?php

namespace App\Models;

use App\Models\ExtendedCollection;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
  /**
     * Create a new Eloquent Collection instance.
     *
     * @param  array  $models
     * @return \App\Models\ExtendedCollection
     */
    public function newCollection(array $models = [])
    {
        return new ExtendedCollection($models);
    }
}
