<?php

namespace App\Console\Commands;

use App\Models\AiScore;
use App\Models\Customer;
use App\Models\Lead;
use App\Services\Ai\ChurnPredictionService;
use App\Services\Ai\CustomerHealthService;
use App\Services\Ai\LeadScoringService;
use Illuminate\Console\Command;

class ComputeAiScores extends Command
{
    protected $signature = 'ai:compute-scores';
    protected $description = 'Persist lead scores, customer health and churn risk to ai_scores.';

    public function handle(LeadScoringService $leads, CustomerHealthService $health, ChurnPredictionService $churn): int
    {
        Lead::open()->get()->each(function (Lead $lead) use ($leads) {
            AiScore::updateOrCreate(
                ['scoreable_type' => Lead::class, 'scoreable_id' => $lead->id, 'score_type' => 'lead_score'],
                ['score' => $leads->score($lead), 'computed_at' => now()],
            );
        });

        Customer::active()->get()->each(function (Customer $customer) use ($health, $churn) {
            AiScore::updateOrCreate(
                ['scoreable_type' => Customer::class, 'scoreable_id' => $customer->id, 'score_type' => 'health_score'],
                ['score' => $health->score($customer), 'computed_at' => now()],
            );

            $risk = $churn->predict($customer);

            AiScore::updateOrCreate(
                ['scoreable_type' => Customer::class, 'scoreable_id' => $customer->id, 'score_type' => 'churn_risk'],
                ['score' => $risk['score'], 'metadata' => ['level' => $risk['level'], 'reasons' => $risk['reasons']], 'computed_at' => now()],
            );
        });

        $this->info('AI scores computed.');

        return self::SUCCESS;
    }
}