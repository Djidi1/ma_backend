<?php

namespace App\Http\Controllers\API;

use App\Audit;
use App\AuditObject;
use App\User;
use App\Settings;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Mail;
use App\Mail\TaskMail;

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
        $audits = Audit::with('checklist', 'user', 'audit_object.audit_object_group', 'audit_result')
            ->where('user_id', $user->id)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                ->from('audit_results')
                ->whereRaw('audits.id = audit_results.audit_id');
            })
           // ->toSql();
            ->get();
        return response()->json($audits);
    }


    public function putAudits(Request $request)
    {
        $user = Auth::user();
        $data = $request->json()->all();
        if (isset($data['audit'])) {
            $object_id = $data['audit']['object_id'];
            $audit_title = $data['audit']['title'] || '';
            $audit_add_date = $data['audit']['date_add'];
            $audit_comment = $data['audit']['comment'];
            $audit_comment = isset($audit_comment[0]['text']) ? $audit_comment[0]['text'] : '';
            $audit_id = 0;
            foreach ($data['audit']['check_list'] as $check_list){
                $check_list_id = $check_list['id'];
                $audit_id = $check_list['audit_id'];
                // Если нет id аудита, то создаем его
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
                                $file_url = "/img/attaches/attache-".time().rand(100,999).".".$extension;
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
                        // Если результат неудовлетворительный:
                        if ($status < 0) {
                            // 1. Создаем задание на устранение
                            $end_date = Carbon::now()->addWeeks(2);
                            $task_id = DB::table('tasks')->insertGetId(
                                [
                                    'result_id' => $audit_result_id,
                                    'task_status_id' => 1,
                                    'done_percent' => 0,
                                    'comment' => $comment_text,
                                    'start' => Carbon::now(),
                                    'end' => $end_date,
                                    'created_at' => Carbon::now(),
                                    'updated_at' => Carbon::now()
                                ]
                            );
                            // 2. Отправляем ответственному лицу сообщение на почту
                            if ($task_id > 0) {
                                // Ищем группу объектов
                                $object = AuditObject::where('id','=',$object_id)->find(1);
                                // Ищем ответственных по требованию
                                $responsible = DB::table('responsible')
                                    ->join('users', 'responsible.user_id', '=', 'users.id')
                                    ->whereRaw("REPLACE(REPLACE(users.object_group_id, '[', ','), ']', ',') LIKE '%,{$object['audit_object_group_id']},%'")
                                    ->whereRaw("REPLACE(REPLACE(responsible.requirement_id, '[', ','), ']', ',') LIKE '%,$requirement_id,%'")->first();
                                // Если не нашли, то ищем по объекту
                                if ($responsible == null) {
                                    $responsible = DB::table('responsible')
                                        ->whereRaw("REPLACE(REPLACE(object_id, '[', ','), ']', ',') LIKE '%,$object_id,%'")->first();
                                }
                                $settings = Settings::find(1);
                                // Отправляем письмо, если нашли ответственного
                                if ($responsible !== null) {
                                    $user = User::find($responsible->user_id);
                                    Mail::to($user)->send(new TaskMail($user, $settings->subject, $settings->body, $task_id, $comment_text, $end_date));
                                } else {
                                    $user = Auth::user();
                                }
                                // Система гарантий качества
                                Mail::to('djidi@mail.ru')
                                    ->send(new TaskMail($user, $settings->subject, $settings->body, $task_id, $comment_text, $end_date));
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
