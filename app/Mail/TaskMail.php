<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
//use Illuminate\Contracts\Queue\ShouldQueue;

class TaskMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $subject;
    public $body;
    public $task_id;
    public $comment;
    public $object;
    public $requirement;
    public $end_date;

    /**
     * Create a new message instance.
     *
     * @param $user
     * @param $subject
     * @param $body
     * @param $task_id
     * @param $comment
     * @param $object
     * @param $requirement
     * @param $end_date
     */
    public function __construct($user, $subject, $body, $task_id, $comment, $end_date, $object, $requirement)
    {
        $this->user = $user;
        $this->subject = $subject;
        $this->body = $body;
        $this->task_id = $task_id;
        $this->comment = $comment;
        $this->object = $object;
        $this->requirement = $requirement;
        $this->end_date = $end_date;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)->view('taskmail');
    }
}
