<?php

namespace App\Http\Controllers;

use App\TaskComment;
use Illuminate\Http\Request;

class TaskCommentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $task_comment = TaskComment::with('task_comment_attache')->get();
        return compact('task_comment');

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
        $result = TaskComment::create($requestData);
        // Если есть вложения, то сохраняем их в БД
        /*
        if (isset($comments[0]['attachments'])) {
            foreach($comments[0]['attachments'] as $attachments){
                $file_name = $attachments['file']['name'];
                $file_size = $attachments['file']['size'];
                $file_mime = $attachments['file']['type'];
                $extension = explode('/', $file_mime )[1];
                $data = $attachments['url'];
                $data = substr($data, strpos($data, ",")+1);
                $file_url = "/img/attaches/attache-".time().rand(100,999).".".$extension;
                $file_path = public_path(). $file_url;
                // Сохраняем фото
                Image::make(base64_decode($data))->save($file_path);

                DB::table('task_comment_attaches')->insert(
                    [
                        'audit_result_id' => $audit_result_id,
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
        */
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
        $result = TaskComment::where('id', $request->id)->update($requestData);
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
        $result = TaskComment::where('id', $request->id)->delete();
        return $result;
    }
}
