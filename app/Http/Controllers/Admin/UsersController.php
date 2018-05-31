<?php

namespace App\Http\Controllers\Admin;

use App\Role;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUsersRequest;
use App\Http\Requests\Admin\UpdateUsersRequest;
use Illuminate\Support\Facades\Hash;

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
        $users = User::with('role')->get();

        return compact('users', 'roles');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $requestData = $request->all();
        if (trim($request->password) != '') {
            $requestData['password'] = Hash::make($request->password);
        }
        $result = User::create($requestData);
        return $result;
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return bool
     */
    public function update(Request $request)
    {
        $requestData = $request->all();
        if (trim($request->password) != '') {
            $requestData['password'] = Hash::make($request->password);
        }
        unset ($requestData['id']);
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
