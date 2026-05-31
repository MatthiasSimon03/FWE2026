<?php

namespace App\Models;

use CodeIgniter\Model;

class SubmissionModel extends Model
{
    protected $table = 'submissions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = ['user_id', 'task_id', 'submitted_answer', 'is_correct', 'awarded_points', 'created_at'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = '';

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

    public function hasCorrectSubmission($userId, $taskId)
    {
        return $this->where('user_id', $userId)
                ->where('task_id', $taskId)
                ->where('is_correct', 1)
                ->first() !== null;
    }

    public function getUserScoreForRally($userId, $rallyId)
    {
        $db = \Config\Database::connect();
        $result = $db->query("
            SELECT SUM(s.awarded_points) AS total_points
            FROM submissions s
            JOIN tasks t ON s.task_id = t.id
            JOIN stations st ON t.station_id = st.id
            WHERE s.user_id = ? AND st.rally_id = ?
        ", [$userId, $rallyId])->getRow();

        return $result->total_points ?? 0;
    }

    public function getLeaderboardForRally($rallyId)
    {
        $db = \Config\Database::connect();
        return $db->query("
            SELECT u.id, u.name, SUM(s.awarded_points) AS score, COUNT(s.id) AS submissions_count
            FROM users u
            JOIN submissions s ON s.user_id = u.id
            JOIN tasks t ON s.task_id = t.id
            JOIN stations st ON t.station_id = st.id
            WHERE st.rally_id = ?
            GROUP BY u.id
            ORDER BY score DESC, COUNT(s.id) ASC
        ", [$rallyId])->getResult('array');
    }
}
