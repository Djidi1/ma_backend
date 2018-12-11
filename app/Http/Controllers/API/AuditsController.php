<?php

namespace App\Http\Controllers\API;

use App\Audit;
use App\AuditObject;
use App\Requirement;
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
        $settings = Settings::find(1);
        $data = $request->json()->all();
        if (isset($data['audit'])) {
            $object_id = $data['audit']['object_id'];
            $audit_title = $data['audit']['title'] || '';
            $audit_add_date = $data['audit']['date_add'];
            $audit_comment = $data['audit']['comment'];
            $audit_comment = isset($audit_comment[0]['text']) ? $audit_comment[0]['text'] : '';
            $audit_id = 0;
            // Ищем объект
            $object = AuditObject::find($object_id);

            // for SendMail Debugging
            //Mail::to($user)->send(new TaskMail($user, $settings->mail_subject, $settings->mail_body, 1, 'common', '00', $object, ['title'=>'test']));

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
                        $resp_ids = [];
                        $requirement_id = $requirement['id'];
                        $status = $requirement['status'];
                        $comments = $requirement['comments'];
                        $resp_ids[] = $requirement['responsible'];
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
                            // 0. Указываем ответственных с портала
                            // Ищем требование
                            $requirement = Requirement::find($requirement_id);
                            
                            // Ищем по группе объектов и требованиям
                            $resp_grp_obj = DB::table('responsible')
                                ->join('users', 'responsible.user_id', '=', 'users.id')
                                ->whereRaw("REPLACE(REPLACE(responsible.requirement_id, '[', ','), ']', ',') LIKE '%,$requirement_id,%'")
                                ->whereRaw("REPLACE(REPLACE(users.object_group_id, '[', ','), ']', ',') LIKE '%,{$object['audit_object_group_id']},%'")->get();

                            // Ищем ответственных по требованию
                            $resp_req = DB::table('responsible')
                                ->join('users', 'responsible.user_id', '=', 'users.id')
                                ->whereRaw("REPLACE(REPLACE(responsible.requirement_id, '[', ','), ']', ',') LIKE '%,$requirement_id,%'")->get();

                            // Ищем по объекту
                            $resp_obj = DB::table('responsible')
                                ->whereRaw("REPLACE(REPLACE(object_id, '[', ','), ']', ',') LIKE '%,$object_id,%'")->get();

                            if ($resp_ids[0] == 0) {
                                // Если не назначался ответственный в процессе аудита, то задачу получает ответственный за конкретное требование на конкретном объекте, привязанном к конкретной группе объектов аудита
                                foreach ($resp_grp_obj as $resp) {
                                    $resp_ids[] = $resp->user_id;
                                }
                                // Если не назначался ответственный в процессе аудита или если нет ответственных с привязкой к конкретным группам объектов аудита, то задачу и оповещение по почте получает этот ответственный, независимо от того, где проводился аудит
                                if (!isset($resp_ids[1])) {
                                    foreach ($resp_req as $resp) {
                                        $resp_ids[] = $resp->user_id;
                                    }
                                }
                            }
                            // если не назначается ответственый в процессе и нет ответственных за требования, то всегда получает задачу и оповещение на почту ответственный за объект аудита
                            if ($resp_ids[0] == 0 and !isset($resp_ids[1])) {
                                foreach ($resp_obj as $resp) {
                                    $resp_ids[] = $resp->user_id;
                                }
                            }

                            // Ответственные за объект всегда получают уведомления, но в них указываается кто назначен
                            $resp_obj_ids = [];
                            foreach ($resp_obj as $resp) {
                                $resp_obj_ids[] = $resp->user_id;
                            }

                            // 1. Создаем задание на устранение
                            $end_date = Carbon::now()->addWeeks(2);
                            $task_id = DB::table('tasks')->insertGetId(
                                [
                                    'result_id' => $audit_result_id,
                                    'responsible_id' => isset($resp_ids[0]) ? $resp_ids[0] : $user->id, // Если не нашли ответственных, то указываем аудитора
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
                                // Отправляем письмо, если указан ответственный
                                foreach (array_unique($resp_ids) as $resp_id) {
                                    if ($resp_id > 0) {
                                        $resp_user = User::find($resp_id);
                                        Mail::to($resp_user)->send(new TaskMail($resp_user, $settings->mail_subject, $settings->mail_body, $task_id, $comment_text, $end_date, $object, $requirement));
                                    }
                                }
                                foreach (array_unique($resp_obj_ids) as $resp_obj_id) {
                                    if ($resp_id > 0) {
                                        $resp_obj_user = User::find($resp_obj_id);
                                        foreach (array_unique($resp_ids) as $resp_id) {
                                            if ($resp_id > 0) {
                                                $resp_user = User::find($resp_id);
                                                Mail::to($resp_obj_user)->send(new TaskMail($resp_user, $settings->mail_subject, $settings->mail_body, $task_id, $comment_text, $end_date, $object, $requirement));
                                            }
                                        }
                                    }
                                }
                                // Система гарантий качества
                                Mail::to('djidi@mail.ru')
                                    ->send(new TaskMail($user, "!>" . $settings->mail_subject, $settings->mail_body, $task_id, $comment_text, $end_date, $object, $requirement));
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
