<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class TaskMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $task_id;
    public $comment;
    public $end_date;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $task_id, $comment, $end_date)
    {
        $this->user = $user;
        $this->task_id = $task_id;
        $this->comment = $comment;
        $this->end_date = $end_date;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('taskmail');
    }
}
