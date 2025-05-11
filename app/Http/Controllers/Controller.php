<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Closure;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected $beforeActions = [];

    public function __construct()
    {
        $this->beforeActions = collect([]);
    }

    protected function beforeAction(Closure $callback)
    {
        $this->beforeActions->push($callback);
    }

    public function callAction($method, $parameters)
    {
        foreach ($this->beforeActions as $action) {
            $result = $action(request());
            if ($result) {
                return $result;
            }
        }

        return parent::callAction($method, $parameters);
    }
}
