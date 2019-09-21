<?php
namespace App\Modules\Users\Events;

use HZ\Illuminate\Mongez\Traits\RepositoryTrait;
use App\Modules\Tasks\Repositories\TasksRepository;

class Tasks
{
    use RepositoryTrait;

    /**
     * Add task to its participants and supervisors
     * 
     * @param   \App\Modules\Tasks\Models\Task $task
     * @param   \Illuminate\Http\Request $request
     * @return  void
     */
    public function onCreatingTask($task, $request)
    {
        list($participant, $supervisor) = $this->getParticipantAndSupervisor($task);

        $participant->associate($task, 'tasks')->save();

        if ($supervisor) {
            $supervisor->associate($task, 'supervisingTasks')->save();
        }
    }

    /**
     * Update the task to its participants and supervisors
     * 
     * @param   \App\Modules\Tasks\Models\Task $task
     * @param   \Illuminate\Http\Request $request
     * @param   \App\Modules\Tasks\Models\Task $task
     * @return  void
     */
    public function onUpdatingTask($task, $request, $oldTask)
    {
        $oldParticipant = $this->users->getModel($oldTask->participant['id']);
        $oldSupervisor = $this->users->getModel($oldTask->supervisor['id']);
        if ($task->participant['id'] != $oldTask->participant['id']) {
            // send a notification|email to inform the user about dismissing him/her from the task
            $oldParticipant->disassociate($oldTask, 'tasks')->save();
            // send a notification|email to inform the user about assigning him/her from the task
            $this->users->getModel($task->participant['id'])->associate($task, 'tasks')->save();
        } else {
            // update participant task info
            $participantTasks = $oldParticipant->tasks;
            foreach ($participantTasks as &$participantTask) {
                if ($participantTask['id'] != $task->id) continue;

                foreach (TasksRepository::DATA as $column) {
                    $participantTask[$column] = $task->$column;
                }

                // update supervisor as well
                $participantTask['supervisor'] = $task->supervisor;
            }

            $oldParticipant->tasks = $participantTasks;

            $oldParticipant->save();
        }

        if ($oldTask->supervisor && $task->supervisor['id'] != $oldTask->supervisor['id']) {
            $oldSupervisor->disassociate($oldTask, 'supervisingTasks')->save();
            $this->users->getModel($task->supervisor['id'])->associate($task, 'supervisingTasks')->save();
        } elseif ($task->supervisor) {
            $supervisor = $this->users->getModel($task->supervisor['id']);
            $supervisor->associate($task, 'supervisingTasks')->save();
        } elseif ($oldSupervisor) {
            // update participant task info
            $supervisorTasks = $oldSupervisor->supervisingTasks;
            
            foreach ($supervisorTasks as &$supervisorTask) {
                if ($supervisorTask['id'] != $task->id) continue;

                foreach (TasksRepository::DATA as $column) {
                    $supervisorTask[$column] = $task->$column;
                }

                // update participant as well
                $supervisorTask['participant'] = $task->supervisor;
            }

            $oldSupervisor->supervisorTasks = $supervisorTasks;

            $oldSupervisor->save();
        }
    }

    /**
     * Delete the task from the participants and the supervisors as well
     * 
     * @param   mixed $model
     * @param   int $id
     * @return  void 
     */
    public function onDeletingTask($model)
    {
        list($participant, $supervisor) = $this->getParticipantAndSupervisor($model);

        $participant->disassociate($model, 'tasks')->save();

        $supervisor->disassociate($model, 'supervisingTasks')->save();
    }

    /**
     * Get participant and supervisor
     * 
     * @param   mixed $task
     * @return  array
     */
    protected function getParticipantAndSupervisor($task)
    {
        $participant = $this->users->getModel($task->participant['id']);
        $supervisor = $this->users->getModel($task->supervisor['id']);

        return [$participant, $supervisor];
    }
}