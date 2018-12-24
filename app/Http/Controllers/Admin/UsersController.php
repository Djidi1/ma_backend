<?php

namespace App\Http\Controllers\Admin;

use App\Department;
use App\Position;
use App\Role;
use App\User;
use App\Requirement;
use App\Checklist;
use App\AuditObject;
use App\AuditObjectGroup;
use App\Responsible;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    /**
     * Display a listing of User.
     *
     * @return User[]|\Illuminate\Database\Eloquent\Collection
     */
    public function index()
    {
        $roles = Role::all();
        $objects = AuditObject::all();
        $object_groups = AuditObjectGroup::all();
        $requirements = Requirement::all();
        $checklists = Checklist::all();
        $departments = Department::all();
        $positions = Position::all();
        $users = User::with('role','responsible','department','position')->get();

        return compact('users', 'roles', 'objects', 'object_groups', 'requirements', 'checklists', 'departments', 'positions');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return User[]|\Illuminate\Database\Eloquent\Collection
     */
    public function store(Request $request)
    {
        // Метод доступен только администраторам
        if (Auth::user()->role_id !== 1){
            return response([
                'status' => 'error',
                'msg' => 'Permission denied'
            ], 403);
        }
        $requestData = $request->all();
        $responsible = $requestData['responsible'];
        // Удаляем лишнюю для таблицы пользователей информацию
        unset ($requestData['responsible']);
        unset ($requestData['department']);
        unset ($requestData['position']);

        // Если указан пароль
        if (trim($request->password) != '') {
            $requestData['password'] = Hash::make($request->password);
        }
        $user = User::create($requestData);

        // Сохраняем привязку к ответственным
        if (isset($responsible['id'])) {
            $responsible_item = Responsible::find($responsible['id']);
            $responsible_item->user_id = $user->id;
            $responsible_item->object_id = $responsible['object_id'];
            $responsible_item->requirement_id = $responsible['requirement_id'];
            $responsible_item->save();
        }else {
            $responsible['user_id'] = $user->id;
            Responsible::create($responsible);
        }

        //Получаем созданного пользователя со связями
        $user_data = User::with('role','responsible')->find($user->id);

        return $user_data;
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return bool
     */
    public function update(Request $request)
    {
        // Метод доступен только администраторам
        if (Auth::user()->role_id !== 1){
            return response([
                'status' => 'error',
                'msg' => 'Permission denied'
            ], 403);
        }
        $requestData = $request->all();
        $responsible = $requestData['responsible'];
        // Обновляем пароль
        if (trim($request->password) != '') {
            $requestData['password'] = Hash::make($request->password);
        }
        unset ($requestData['id']);
        // Сохраняем привязку к ответственным
        // Если уже есть, то обновляем, иначе добавляем
        if (isset($responsible['id'])) {
            $responsible_item = Responsible::find($responsible['id']);
            $responsible_item->user_id = $request->id;
            $responsible_item->object_id = $responsible['object_id'];
            $responsible_item->requirement_id = $responsible['requirement_id'];
            $responsible_item->save();
        }else {
            $responsible['user_id'] = $request->id;
            Responsible::create($responsible);
        }
        // Удаляем лишнюю для таблицы пользователей информацию
        unset ($requestData['responsible']);
        $requestData['object_group_id'] = json_encode($requestData['object_group_id']);

        $result = User::where('id', $request->id)->update($requestData);
        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @return bool
     */
    public function destroy(Request $request)
    {
        $result = User::where('id', $request->id)->delete();
        return $result;
    }

}
