<?php
namespace App\Modules\Users\Controllers\Admin;

use Illuminate\Http\Request;
use HZ\Illuminate\Organizer\Managers\ApiController;

class TasksController extends ApiController
{
    /**
     * User model object
     * 
     * @var \App\Modules\Users\Models\User 
     */
    protected $user;

    /**
     * Update user task checklist
     *
     * @param   int $taskId
     * @param   \Illuminate\Http\Request $request
     * @return  mixed
     */
    public function updateChecklist($taskId, Request $request)
    {
        $this->user = user();
        
        $this->tasks->getModel($taskId)->updateChecklist($taskId, $request->checklist)->save();

        $this->user->tasksList()->updateChecklist($taskId, $request->checklist)->save();

        return $this->successUser();
    }

    /**
     * Mark the task as completed by the user
     *
     * @param   int $taskId
     * @param   \Illuminate\Http\Request $request
     * @param   string $taskStatus
     * @return  mixed
     */
    public function markStatusAsCompletedByUser($taskId, $taskStatus)
    {
        $this->user = user();

        $this->user->tasksList()->updateStatus($taskId, $taskStatus)->save();

        return $this->successUser();
    }

    /**
     * Return success response with user object
     * 
     * @return mixed
     */
    protected function successUser()
    {
        $usersRepository = repo('users');

        return $this->success([
            'user' => $usersRepository->wrap($this->user),
        ]);
    }
}