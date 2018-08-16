<?php
namespace App\Http\Controllers;

use App\Traits\RepositoryTrait;

class HomeController extends Controller
{   
    use RepositoryTrait;
    /**
     * Home page
     * 
     * @return string
     */
    public function index()
    {
        $users = $this->users->list([
            'select' => ['id', 'name', 'email'],
            'orderBy' => ['name', 'DESC'],
        ]);

        pred($users);
    }
}
