<?php

namespace App\Http\Controllers\API;

use App\Audit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;

class AuditsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAudits(Request $request)
    {
        $user = Auth::user();
        $audits = Audit::with('checklist', 'user', 'audit_object')->where('user_id', $user->id)->get();
        return response()->json($audits);
    }
    public function putAudits(Request $request)
    {
        $user = Auth::user();
        $data = $request->json()->all();
        if (isset($data['audit'])) {
            $object_id = $data['audit']['object_id'];
            $audit_title = $data['audit']['title'];
            $audit_add_date = $data['audit']['date_add'];
            $audit_comment = $data['audit']['comment'];
            $audit_comment = isset($audit_comment[0]['text']) ? $audit_comment[0]['text'] : '';
            $audit_id = 0;
            foreach ($data['audit']['check_list'] as $check_list){
                $check_list_id = $check_list['id'];
                $audit_id = $check_list['audit_id'];
                // Если нет ид аудита, то создаем его
                if ( $audit_id == 0 ) {
                    $audit_id = DB::table('audits')->insertGetId(
                        [
                            'title' => $audit_title,
                            'date_add' => Carbon::parse($audit_add_date),
                            'date' => Carbon::parse($audit_add_date),
                            'user_id' => $user->id,
                            'checklist_id' => $check_list_id,
                            'object_id' => $object_id,
                            'comment' => $audit_comment,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]
                    );
                }
                // Если аудит создан, то проставляем по нему результаты проверок
                if ( $audit_id > 0 ) {
                    foreach ($check_list['requirement'] as $requirement) {
                        $requirement_id = $requirement['id'];
                        $status = $requirement['status'];
                        $comments = $requirement['comments'];
                        $comment_text = isset($comments[0]['text']) ? $comments[0]['text'] : '';
                        $audit_result_id = DB::table('audit_results')->insertGetId(
                            [
                                'audit_id' => $audit_id,
                                'requirement_id' => $requirement_id,
                                'result' => $status,
                                'comment' => $comment_text,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now()
                            ]
                        );
                        // Если есть вложения, то сохраняем их в БД
                        if (isset($comments[0]['attachments'])) {
                            foreach($comments[0]['attachments'] as $attachments){
                                $file_name = $attachments['file']['name'];
                                $file_size = $attachments['file']['size'];
                                $file_mime = $attachments['file']['type'];
                                $extension = explode('/', $file_mime )[1];
                                $data = $attachments['url'];
                                $data = substr($data, strpos($data, ",")+1);
                                $file_url = "/img/attaches/attache-".time().".".$extension;
                                $file_path = public_path(). $file_url;
                                // Сохраняем фото
                                Image::make(base64_decode($data))->save($file_path);

                                DB::table('audit_result_attaches')->insert(
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
                    }
                }
            }
            return $audit_id;
        }else{
            return 0;
        }
    }

}
