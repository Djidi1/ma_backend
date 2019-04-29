<?php

namespace App\Http\Controllers;

use App\Checklist;
use App\Requirement;
use App\RequirementGroups;
use App\Responsible;
use Illuminate\Http\Request;
use App\Exports\RequirementsExport;
use App\Imports\RequirementsImport;
use Maatwebsite\Excel\Facades\Excel;

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
        $requirements = Requirement::with('checklists:checklists.id')->withCount(['audit_results' => function($q) {
            $q->groupBy('requirement_id');
        }])->get();        
        $responsible = Responsible::with('user')->get();
        return compact('requirements', 'responsible', 'requirement_groups', 'checklist_groups');

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
        unset ($requestData['checklist_id']);
        $result = Requirement::create($requestData);
        $req = Requirement::find($result->id);
        $sync = $req->checklists()->sync([$request->checklist_id]);
        $req = Requirement::with('checklists:checklists.id')->withCount(['audit_results' => function($q) {
            $q->groupBy('requirement_id');
        }])->find($result->id);
        return $req;
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
        unset ($requestData['checklists']);
        $result = Requirement::where('id', $request->id)->update($requestData);
        $req = Requirement::with('checklists:checklists.id')->withCount(['audit_results' => function($q) {
            $q->groupBy('requirement_id');
        }])->find($request->id);
        $sync = $req->checklists()->sync($request->checklists);
        return $req;
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

    /**
     * @return \Illuminate\Support\Collection
     */
    public function export() 
    {
        return Excel::download(new RequirementsExport, 'Requirements_' . date('d.m.Y') .'.xlsx');
    }
   
    /**
     * @return \Illuminate\Support\Collection
     */
    public function import() 
    {
        $result = Excel::import(new RequirementsImport,request()->file('file'));
        return 1;
    }
}
