<?php

namespace App\Models;

use CodeIgniter\Model;

class TaskModel extends Model
{
    protected $table = 'tasks';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = ['station_id', 'text', 'answer', 'points', 'answer_type', 'meta'];
    protected $useTimestamps    = false;
    public function getTasksByStation($stationId)
    {
        return $this->where('station_id', $stationId)->findAll();
    }

    public function evaluateAnswer($taskId, $submittedAnswer)
    {
        $task = $this->find($taskId);
        if (!$task) {
            return ['correct' => false, 'points' => 0];
        }

        $answerType = $task['answer_type'];
        $correct = false;

        switch ($answerType) {
            case 'text':
            case 'number': // NEU: Behandelt Zahlen genau wie Text
                // Case-insensitive, trimmed comparison (mit mb_ für Umlaute wie 'Löwe')
                $correct = mb_strtolower(trim((string)$submittedAnswer)) === mb_strtolower(trim((string)$task['answer']));
                break;

            case 'regex':
                // Regex matching
                if ($task['answer']) {
                    $correct = preg_match($task['answer'], $submittedAnswer) === 1;
                }
                break;

            case 'multiple_choice':
                // Einfacher Textvergleich für Multiple-Choice-Antworten
                $correct = mb_strtolower(trim((string)$submittedAnswer)) === mb_strtolower(trim((string)$task['answer']));
                break;

        }

        return [
            'correct' => $correct,
            'points' => $correct ? $task['points'] : 0
        ];
    }
}