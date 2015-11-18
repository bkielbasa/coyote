<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

class Microblog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['parent_id', 'user_id', 'text'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    public function getMediaAttribute($media)
    {
        return json_decode($media, true);
    }

    public function setMediaAttribute($media)
    {
        $this->attributes['media'] = json_encode($media);
    }
}
