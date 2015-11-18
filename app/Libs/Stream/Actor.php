<?php

namespace Coyote\Stream;

use Coyote\Stream\Objects\Object;
use Coyote\User;

class Actor extends Object
{
    public $displayName;
    public $id;
    public $url;

    public function __construct(User $user, array $data = [])
    {
        if ($user) {
            $data = array_merge([
                'displayName'   => $user->name,
                'id'            => $user->id,
                'url'           => route('profile', ['user' => $this->id])
            ]);
        }

        parent::__construct($data);
    }
}