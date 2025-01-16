<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('home');

    // Маршруты для компаний
    $router->resource('companies', CompanyController::class);

    // Маршруты для саун
    $router->resource('saunas', SaunaController::class);

    // Маршруты для расписания
    $router->resource('schedules', ScheduleController::class);

    // Маршруты для клиентов
    $router->resource('clients', ClientController::class);
});
