<?php

namespace App\Http\Controllers;

use App\Audit;
use App\AuditResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditResultsController extends Controller
{

    public function getResults(Request $request)
    {

        $results = AuditResult::with('audit', 'requirement', 'audit_result_attache')
        ->whereHas('audit', function ($query) {
            $user = Auth::user();
            $query->where('user_id', $user->id);
        })->get();
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
        if ($audit_id > 0) {
            $audit_results = AuditResult::with('audit', 'requirement', 'audit_result_attache')->
                        where('audit_id', $audit_id)->
                        where('result', '!=', '1')->
                        get();
            $audits = Audit::all();
            return compact('audit_results', 'audits');
        } else {
            return 'false';
        }
    }

}
