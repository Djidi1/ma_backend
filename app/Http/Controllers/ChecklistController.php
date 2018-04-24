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
        $checklists = Checklist::with('cl_category','requirement')->get();
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
        $checklists = Checklist::with('cl_category','requirement')->get();
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
        $result = Checklist::create($requestData);
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
