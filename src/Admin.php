<?php

namespace SmallRuralDog\Admin;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use SmallRuralDog\Admin\Controllers\AuthController;

class Admin
{
    public static $metaTitle;

    public static function setTitle($title)
    {
        self::$metaTitle = $title;
    }

    /**
     * Get admin title.
     *
     * @return string
     */
    public function title()
    {
        return self::$metaTitle ? self::$metaTitle : config('admin.title');
    }

    public function user()
    {
        return $this->guard()->user();
    }

    /**
     * Attempt to get the guard from the local cache.
     *
     * @return \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard
     */
    public function guard()
    {
        $guard = config('admin.auth.guard') ?: 'admin';

        return Auth::guard($guard);
    }


    public function routes()
    {
        $attributes = [
            'prefix' => config('admin.route.prefix'),
            'middleware' => config('admin.route.middleware'),
        ];
        app('router')->group($attributes, function ($router) {
            /* @var Route $router */
            $router->namespace('\Encore\Admin\Controllers')->group(function ($router) {
                /* @var Router $router */
                $router->resource('auth/users', 'UserController')->names('admin.auth.users');
                $router->resource('auth/roles', 'RoleController')->names('admin.auth.roles');
                $router->resource('auth/permissions', 'PermissionController')->names('admin.auth.permissions');
                $router->resource('auth/menu', 'MenuController', ['except' => ['create']])->names('admin.auth.menu');
                $router->resource('auth/logs', 'LogController', ['only' => ['index', 'destroy']])->names('admin.auth.logs');

                $router->post('_handle_form_', 'HandleController@handleForm')->name('admin.handle-form');
                $router->post('_handle_action_', 'HandleController@handleAction')->name('admin.handle-action');
            });
            $authController = config('admin.auth.controller', AuthController::class);
            /* @var Router $router */
            $router->get('auth/login', $authController . '@getLogin')->name('admin.login');
            $router->post('auth/login', $authController . '@postLogin')->name('admin.post.login');
            $router->get('auth/logout', $authController . '@getLogout')->name('admin.logout');
            $router->get('auth/setting', $authController . '@getSetting')->name('admin.setting');
            $router->put('auth/setting', $authController . '@putSetting');
        });
    }
}