<?php

namespace Coyote\Repositories\Criteria\Topic;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Coyote\Repositories\Contracts\RepositoryInterface;
use Coyote\Repositories\Criteria\Criteria;
use Coyote\User;

class OnlyThoseWithAccess extends Criteria
{
    /**
     * @var array
     */
    private $groupsId = [];

    /**
     * @param \Coyote\User|null $user
     */
    public function __construct($user = null)
    {
        if ($user instanceof User) {
            $this->groupsId = $user->getGroupsId();
        }
    }

    /**
     * @param $model
     * @param RepositoryInterface $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        return $model->whereNested(function ($sub) use ($model) {
            $sub->whereNotExists(function ($sub) {
                return $sub->select('forum_id')
                    ->from('forum_access')
                    ->where('forum_access.forum_id', '=', \DB::raw('topics.forum_id'));
            });

            if (!empty($this->groupsId)) {
                $sub->orWhereExists(function ($sub) {
                    return $sub->select('forum_id')
                        ->from('forum_access')
                        ->whereIn('group_id', $this->groupsId)
                        ->where('forum_access.forum_id', '=', \DB::raw('topics.forum_id'));
                });
            }
        });
    }
}
