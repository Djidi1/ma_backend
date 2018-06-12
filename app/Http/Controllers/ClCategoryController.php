<?php

namespace App\Http\Controllers;

use App\ClCategory;
use Illuminate\Http\Request;

class ClCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return ClCategory[]|\Illuminate\Database\Eloquent\Collection
     */

    public function index()
    {
        return ClCategory::with('checklists')->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $result = ClCategory::create($request->all());
        $cl_category = ClCategory::with('checklists')->where('id', $result->id)->first();
        return $cl_category;
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
        $result = ClCategory::where('id', $request->id)->update($requestData);
        $cl_category = ClCategory::with('checklists')->where('id', $request->id)->first();
        return $cl_category;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @return bool
     */
    public function destroy(Request $request)
    {
        $result = ClCategory::where('id', $request->id)->delete();;
        return $result;
    }
}
