<?php
namespace App\Modules\Users\Helpers;

use App\Modules\Users\Models\User;
use Illuminate\Support\Arr;

class TasksList 
{
    /**
     * User model
     * 
     * @var \App\Model\User\User
     */
    private $user;

    /**
     * Tasks list
     * 
     * @var array
     */
    protected $tasks = [];

    /**
     * Constructor
     * 
     * @param  \App\Model\User\User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->tasks = $this->user->tasks;
    }

    /**
     * Update user task checklist items whether if it is checked/done or note
     * 
     * @param   int $taskId
     * @param   array $checklist 
     * @return  $this
     */
    public function updateChecklist($taskId, $checklist) 
    {
        $task = (object) $this->get($taskId);

        $completedChecklist = $task->completedChecklist ?? [];
        $checklistLog = $task->checklistLog ?? [];

        foreach ($checklist as $checklistItem) {
            if (in_array($checklistItem['checklistItem'], $completedChecklist) && $checklistItem['checked'] == 'false') {
                $completedChecklist = Arr::remove($completedChecklist, $checklistItem['checklistItem']);
            } elseif ($checklistItem['checked']) {
                $completedChecklist[] = $checklistItem['checklistItem'];
            }
           
            $checklistItemLog = $checklistItem;
            
            $checklistItemLog['createdAt'] = time();

            $checklistLog[] = $checklistItemLog; 
        }

        $task->completedChecklist = $completedChecklist;
        $task->checklistLog = $checklistLog;

        $this->update($task);

        return $this;
    }

    /**
     * Update task status
     *
     * @param int $taskId
     * @param string $status
     * @return void
     */
    public function updateStatus(int $taskId, string $status): TasksList
    {
        $task = $this->get($taskId);

        $task['status'] = $status;

        return $this;
    }

    /**
     * Get user task for the given task id
     * 
     * @param   int $taskId
     * @return  array
     */
    public function get($taskId)
    {
        foreach ($this->tasks as $task) {
            if ($task['id'] == $taskId) return $task;
        }
    }

    /**
     * Update the given task
     * 
     * @param   mixed $task
     * @return  $this
     */
    public function update($task)
    {
        $task = (object) $task;

        foreach ($this->tasks as &$userTask) {
            if ($userTask['id'] == $task->id) {
                $userTask = $task;
                break;
            }
        }

        return $this;
    }

    /**
     * Save user tasks
     * 
     * @return  void
     */
    public function save()
    {
        $this->user->tasks = $this->tasks;

        $this->user->save();
    }
}