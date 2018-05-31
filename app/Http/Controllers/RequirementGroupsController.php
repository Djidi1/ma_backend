<?php

namespace App\Http\Controllers;

use App\Requirement;
use App\RequirementGroups;
use Illuminate\Http\Request;

class RequirementGroupsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return RequirementGroups[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function index(Request $request)
    {
        return RequirementGroups::with('requirement')->get();

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
        $result = RequirementGroups::create($requestData);
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
        unset ($requestData['id']);
        $result = RequirementGroups::where('id', $request->id)->update($requestData);
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
        $result = RequirementGroups::where('id', $request->id)->delete();
        return $result;
    }
}
