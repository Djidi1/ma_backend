<?php

namespace App\Http\Controllers;

use App\Checklist;
use App\Requirement;
use App\RequirementGroups;
use Illuminate\Http\Request;

class RequirementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $checklist_groups = Checklist::all();
        $requirement_groups = RequirementGroups::all();
        $requirements = Requirement::all();
        return compact('requirements', 'requirement_groups', 'checklist_groups');

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
        $result = Requirement::create($requestData);
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
        $result = Requirement::where('id', $request->id)->update($requestData);
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
        $result = Requirement::where('id', $request->id)->delete();
        return $result;
    }
}
