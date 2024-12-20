<?php

namespace HXM\LaravelPlanning\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;

class BaseController extends Controller
{
    private $breadcrumb = [];
    protected function view(string $view, array $data = [], array $mergeData = [])
    {
        return view("laravel_planning::" . $view, $data, $mergeData);
    }

    public function callAction($method, $parameters)
    {
        View::share('breadcrumb', $this->breadcrumb);
        return parent::callAction($method, $parameters);
    }

    protected function addBreadcrumb(string $name, string $url = null)
    {
        $this->breadcrumb[] = ['name' => $name, 'url' => $url];
    }
}
