<?php

namespace App\Http\Controllers;

use App\Responsible;
use App\User;
use App\AuditObject;
use App\AuditObjectGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ObjectsController extends Controller
{
    // For API
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getObjects(Request $request)
    {
        //        $user = Auth::user();
        $objects = AuditObject::with('audit_object_group', 'user')->get();
        return response()->json($objects);
    }

    public function index(Request $request)
    {
        $object_groups = AuditObjectGroup::all();
        $objects = AuditObject::with('audit_object_group', 'audit')->get();
        $responsible = Responsible::with('user')->get();
        return compact('objects', 'object_groups', 'responsible');

        //        return response()->json($checklists);
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
        $result = AuditObject::create($requestData);
        $object = AuditObject::with('audit_object_group', 'audit')->where('id', $result->id)->first();
        return $object;
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
        unset ($requestData['id']);
        $result = AuditObject::where('id', $request->id)->update($requestData);
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
        $result = AuditObject::where('id', $request->id)->delete();
        return $result;
    }
}
