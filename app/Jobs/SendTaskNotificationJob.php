<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use App\Mail\TaskNotificationMail;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendTaskNotificationJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    protected $task;
    protected int $userId;

    /**
     * Create a new job instance.
     */
    public function __construct($task, int $userId)
    {
        $this->task = $task;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = User::find($this->userId);

        if (! $user) {
            return;
        }

        Mail::to($user->email)->send(new TaskNotificationMail($this->task));

    }
}
