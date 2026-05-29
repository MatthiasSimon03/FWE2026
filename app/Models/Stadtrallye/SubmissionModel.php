<?php

namespace App\Models\Stadtrallye;

class SubmissionModel extends \App\Models\SubmissionModel
{
    public function submitAnswer($userId, $taskId, $submittedAnswer)
    {
        $taskModel = new TaskModel();
        $evaluation = $taskModel->evaluateAnswer($taskId, $submittedAnswer);

        return $this->insert([
            'user_id' => $userId,
            'task_id' => $taskId,
            'submitted_answer' => $submittedAnswer,
            'is_correct' => $evaluation['correct'] ? 1 : 0,
            'awarded_points' => $evaluation['points']
        ]);
    }
}

