<?php

namespace LaraService\Services;

use Illuminate\Support\Facades\App;

class LaraListService
{
    /**
     * @param $repository
     * @param bool $active
     * @return array|mixed
     */
    public function findList($repository, $active = true)
    {
        if (is_string($repository)) {
            return $this->findListBased($repository, $active);
        }

        $results = [];
        foreach ($repository as $class) {
            $results[] = $this->findListBased($class, $active);
        }

        return $results;
    }

    /**
     * @param $class
     * @param bool $active
     * @return mixed
     */
    public function findListBased($class, $active = true) {
        $repo = App::make($class);
        return $repo->findList($active);
    }
}
