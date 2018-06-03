<?php

namespace App\Http\Controllers;

use App\User;
use App\Task;
use App\TaskComment;
use App\AuditObjectGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class TasksController extends Controller
{

    public function index(Request $request)
    {
        $tasks = Task::with('task_status')->get();
        return compact('tasks');
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
        $result = Task::create($requestData);
        return $result;
    }

    public function saveTaskComment (Request $request)
    {
        $user = Auth::user();
        $data = $request->json()->all();
        $comment_id = DB::table('task_comments')->insertGetId(
            [
                'message' => $data['comment_message'],
                'task_id' => $data['task_id'],
                'user_id' => $user->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        );
        return TaskComment::where('id', $comment_id)->with('user')->get();
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
        $result = Task::where('id', $request->id)->update($requestData);
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
        $result = Task::where('id', $request->id)->delete();
        return $result;
    }
}
