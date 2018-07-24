<?php

namespace App\Http\Controllers;

use App\Requirement;
use App\Responsible;
use App\TaskStatus;
use App\User;
use App\Task;
use App\TaskComment;
use App\AuditObjectGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;


class TasksController extends Controller
{

    public function index(Request $request)
    {
        $tasks = Task::with('task_status', 'result', 'audit_result_attache', 'result.requirement', 'result.audit.audit_object')->get();
        $users = User::all();
        $object_groups = AuditObjectGroup::all();
        $requirements = Requirement::all();
        $statuses = TaskStatus::all();
        $responsible = Responsible::with('user')->get();

        return compact('tasks', 'responsible', 'users', 'object_groups', 'requirements', 'statuses');
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

    public function getTaskComment (Request $request) 
    {
        return TaskComment::where('task_id', $request->id)->with('user','task_comment_attache')->get();
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
        // Если есть вложения, то сохраняем их в БД
        if (isset($data['files'])) {
            foreach($data['files'] as $file){
                $file_name = $file['name'];
                $file_size = $file['size'];
                $file_mime = $file['type'];
                $extension = explode('/', $file_mime )[1];
                $data = $file['base64data'];
                $data = substr($data, strpos($data, ",")+1);
                $file_url = "/img/task/file-".time().rand(100,999).".".$extension;
                $file_path = public_path(). $file_url;
                // Сохраняем фото
                Image::make(base64_decode($data))->save($file_path);

                DB::table('task_comments_attaches')->insert(
                    [
                        'task_comment_id' => $comment_id,
                        'file_name' => $file_name,
                        'file_path' => $file_url,
                        'file_mime' => $file_mime,
                        'file_size' => $file_size,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]
                );
            }
        }
        return TaskComment::where('id', $comment_id)->with('user','task_comment_attache')->get();
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
