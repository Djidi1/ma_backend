<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
// Вход из Мобильного приложения
Route::post('login', 'API\PassportController@login');
Route::post('register', 'API\PassportController@register');

Route::group(['middleware' => 'auth:api'], function(){
    Route::post('get-details', 'API\PassportController@getDetails');
    Route::post('get-audits', 'API\AuditsController@getAudits');
    Route::post('get-objects', 'ObjectsController@getObjects');
    Route::post('get-checklists', 'ChecklistController@getChecklists');
    Route::post('get-results', 'AuditResultsController@getResults');
    Route::post('get-attache', 'AuditResultsController@getImage');

    Route::post('put-audits', 'API\AuditsController@putAudits');
});

// Регистрация из Админки
Route::post('auth/register', 'AuthController@register');
// Вход из Админки
Route::post('auth/login', 'AuthController@login');

Route::get('auth/refresh', 'AuthController@refresh');

Route::group(['middleware' => 'jwt.auth'], function(){
// Авторизация
    Route::get('auth/user', 'AuthController@user');
    Route::post('auth/logout', 'AuthController@logout');

// Пользователи
    Route::get('users_all', 'Admin\UsersController@index');
    Route::post('users_save', 'Admin\UsersController@store');
    Route::put('users_update/{id}', 'Admin\UsersController@update');
    Route::delete('users_delete/{id}', 'Admin\UsersController@destroy');

// Ответственные
    Route::get('responsible_tasks', 'ResponsibleController@getTasks');
    Route::get('responsible_all', 'ResponsibleController@index');
    Route::post('responsible_save', 'ResponsibleController@store');
    Route::put('responsible_update/{id}', 'ResponsibleController@update');
    Route::delete('responsible_delete/{id}', 'ResponsibleController@destroy');

// Чек-листы
    Route::get('cl_categories_all', 'ClCategoryController@index');
    Route::post('cl_categories_save', 'ClCategoryController@store');
    Route::put('cl_categories_update/{id}', 'ClCategoryController@update');
    Route::delete('cl_categories_delete/{id}', 'ClCategoryController@destroy');

    Route::get('checklists_all', 'ChecklistController@index');
    Route::post('checklists_save', 'ChecklistController@store');
    Route::put('checklists_update/{id}', 'ChecklistController@update');
    Route::delete('checklists_delete/{id}', 'ChecklistController@destroy');

// Требования
    Route::get('requirements_all', 'RequirementController@index');
    Route::post('requirements_save', 'RequirementController@store');
    Route::put('requirements_update/{id}', 'RequirementController@update');
    Route::delete('requirements_delete/{id}', 'RequirementController@destroy');

    Route::get('requirement_groups_all', 'RequirementGroupsController@index');
    Route::post('requirement_groups_save', 'RequirementGroupsController@store');
    Route::put('requirement_groups_update/{id}', 'RequirementGroupsController@update');
    Route::delete('requirement_groups_delete/{id}', 'RequirementGroupsController@destroy');

// Объекты
    Route::get('objects_all', 'ObjectsController@index');
    Route::post('objects_save', 'ObjectsController@store');
    Route::put('objects_update/{id}', 'ObjectsController@update');
    Route::delete('objects_delete/{id}', 'ObjectsController@destroy');

    Route::get('object_groups_all', 'ObjectGroupsController@index');
    Route::post('object_groups_save', 'ObjectGroupsController@store');
    Route::put('object_groups_update/{id}', 'ObjectGroupsController@update');
    Route::delete('object_groups_delete/{id}', 'ObjectGroupsController@destroy');

// Аудиты
    Route::get('audits_all', 'AuditListsController@index');
    Route::post('audits_save', 'AuditListsController@store');
    Route::put('audits_update/{id}', 'AuditListsController@update');
    Route::delete('audits_delete/{id}', 'AuditListsController@destroy');

    Route::get('audit_results_all/{id}', 'AuditResultsController@index');


    Route::get('audit_tasks_all', 'AuditListsController@auditTasksAll');
});
/*
Route::group(['middleware' => 'jwt.refresh'], function(){
    Route::get('auth/refresh', 'AuthController@refresh');
});
*/