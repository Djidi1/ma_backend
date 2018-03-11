<?php

namespace App\Http\Controllers;

use App\Audit;
use App\AuditResult;
use Illuminate\Http\Request;

class AuditResultsController extends Controller
{

    public function getResults(Request $request)
    {
        $results = AuditResult::with('audit', 'requirement', 'audit_result_attache')->get();
        return response()->json($results);
    }
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $audit_id = $request->id;
        $audit_results = AuditResult::with('audit', 'requirement', 'audit_result_attache')->where('audit_id', $audit_id)->get();
        $audits = Audit::all();
        return compact('audit_results', 'audits');
    }

}
