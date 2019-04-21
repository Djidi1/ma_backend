<?php

namespace App\Http\Controllers;

use App\Checklist;
use App\ClCategory;
use Illuminate\Http\Request;

class ChecklistController extends Controller
{
    // For API
    public function getChecklists(Request $request)
    {
        $checklists = Checklist::with(['cl_category', 'requirements' => function($query){
            $query->where('disable', '0');
        }])->get();

        return response()->json($checklists);
    }
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $checklist_categories = ClCategory::all();
        $checklists = Checklist::with('cl_category','requirements')->get();
        return compact('checklists', 'checklist_categories');

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
        $req = Checklist::create($requestData);
        $sync = $req->requirements()->sync($request->requirements);
        $result = Checklist::with('cl_category','requirements')->find($req->id);
        return $result;
    }

    /**
     * Copy resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function copy(Request $request)
    {
        $idList = $request->id_list ?? [];
        if (count($idList) == 0) {
            return response([
                'status' => 'error',
                'error' => 'invalid.data',
                'msg' => 'List of id is empty'
            ], 400);
        }

        $result = [];
        foreach ($idList as $id){
            $item = Checklist::find($id);
            $item->load('requirements', 'cl_category');
            $clone = $item->replicate();
            $clone->title .= ' - копия';
            $clone->save();
            $clone->requirements()->attach($item->requirements);
            $result[] = $clone;
        }
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
        $checklist = Checklist::find($request->id);
        $reqUpd = $checklist->requirements()->sync($request->requirements);
        unset ($requestData['id']);
        unset ($requestData['requirements']);
        $result = Checklist::where('id', $request->id)->update($requestData);
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
        $result = Checklist::where('id', $request->id)->delete();
        return $result;
    }
}
