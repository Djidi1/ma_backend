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
Route::post('login', [ 'as' => 'login', 'uses' => 'API\PassportController@login']);
Route::post('register', [ 'as' => 'login', 'uses' => 'API\PassportController@register']);

Route::group(['middleware' => 'auth:api'], function(){
    Route::post('get-details', 'API\PassportController@getDetails');
    Route::post('get-audits', 'API\AuditsController@getAudits');
    Route::post('get-objects', 'ObjectsController@getObjects');
    Route::post('get-checklists', 'ChecklistController@getChecklists');
    Route::post('get-results', 'AuditResultsController@getResults');
    Route::post('get-attache', 'AuditResultsController@getImage');
	Route::post('get-users', 'Admin\UsersController@index');

    Route::post('put-audits', 'API\AuditsController@putAudits');
});

// Регистрация из Админки
Route::post('auth/register', 'AuthController@register');
// Вход из Админки
Route::post('auth/login', 'AuthController@login');

Route::get('auth/refresh', 'AuthController@refresh');

Route::group(['middleware' => 'jwt.auth'], function(){
// Настройки
    Route::get('get-settings', 'SettingsController@index');
    Route::put('update-settings', 'SettingsController@update');

// Авторизация
    Route::get('auth/user', 'AuthController@user');
    Route::post('auth/logout', 'AuthController@logout');

// Пользователи
    Route::get('users_all', 'Admin\UsersController@index');
    Route::post('users_save', 'Admin\UsersController@store');
    Route::put('users_update/{id}', 'Admin\UsersController@update');
    Route::delete('users_delete/{id}', 'Admin\UsersController@destroy');

    Route::post('users_import', 'Admin\UsersController@import');
    Route::get('users_export', 'Admin\UsersController@export');

// Должности
    Route::get('position_all', 'Admin\PositionController@index');
    Route::post('position_save', 'Admin\PositionController@store');
    Route::put('position_update/{id}', 'Admin\PositionController@update');
    Route::delete('position_delete/{id}', 'Admin\PositionController@destroy');

// Подразделения
    Route::get('department_all', 'Admin\DepartmentController@index');
    Route::post('department_save', 'Admin\DepartmentController@store');
    Route::put('department_update/{id}', 'Admin\DepartmentController@update');
    Route::delete('department_delete/{id}', 'Admin\DepartmentController@destroy');

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
    Route::post('checklists_copy', 'ChecklistController@copy');
    Route::put('checklists_update/{id}', 'ChecklistController@update');
    Route::delete('checklists_delete/{id}', 'ChecklistController@destroy');

// Требования
    Route::get('requirements_all', 'RequirementController@index');
    Route::post('requirements_save', 'RequirementController@store');
    Route::put('requirements_update/{id}', 'RequirementController@update');
    Route::delete('requirements_delete/{id}', 'RequirementController@destroy');

    Route::post('requirements_import', 'RequirementController@import');
    Route::get('requirements_export', 'RequirementController@export');

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

    Route::post('objects_import', 'ObjectsController@import');
    Route::get('objects_export', 'ObjectsController@export');

// Аудиты
    Route::get('audits_all', 'AuditListsController@index');
    Route::post('audits_save', 'AuditListsController@store');
    Route::post('audits_add', 'AuditListsController@add');
    Route::put('audits_update/{id}', 'AuditListsController@update');
    Route::delete('audits_delete/{id}', 'AuditListsController@destroy');
    Route::get('audit_results_all/{id}', 'AuditResultsController@index');
    Route::get('audit_tasks_all', 'AuditListsController@auditTasksAll');

// Задачи
    Route::get('tasks', 'TasksController@index');
    Route::post('task_save', 'TasksController@store');
    Route::put('task_update/{id}', 'TasksController@update');

    Route::get('responsible_tasks_comments/{id}', 'TasksController@getTaskComment');
    Route::post('send_task_comment', 'TasksController@saveTaskComment');
    Route::post('send_task_comment_attache', 'TasksController@saveTaskCommentAttache');

});
/*
Route::group(['middleware' => 'jwt.refresh'], function(){
    Route::get('auth/refresh', 'AuthController@refresh');
});
*/