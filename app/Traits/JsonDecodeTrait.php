<?php

namespace App\Traits;

trait JsonDecodeTrait
{
    /**
     * Decode the JSON representation of the query results.
     *
     * @return mixed
     */
    public function toDecodedJson()
    {

        if (is_null($this)) {
            return null;
        }

        if ($this instanceof \Illuminate\Database\Eloquent\Collection) {
            if ($this->isEmpty()) {
                return [];
            }
            return json_decode($this->toJson());
        } else
            return json_decode($this->toJson());
    }
}
