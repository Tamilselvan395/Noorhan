<?php

namespace Tests\Unit\Dashboard;

use App\Enums\DashboardPeriod;
use App\Enums\LoginHistoryType;
use App\Models\LoginHistory;
use App\Models\User;
use App\Services\Dashboard\ChartDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChartDataServiceTest extends TestCase
{
    use RefreshDatabase;

    private ChartDataService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ChartDataService::class);
    }

    public function test_hourly_buckets_are_zero_filled_and_counted(): void
    {
        $user = User::factory()->create();

        LoginHistory::factory()->count(2)->create([
            'user_id' => $user->id,
            'type' => LoginHistoryType::Login->value,
            'successful' => true,
            'created_at' => now(),
        ]);

        [$from, $to] = DashboardPeriod::Today->range();

        $result = $this->service->counts(
            LoginHistory::query()->where('successful', true),
            $from, $to, 'created_at', true,
        );

        $this->assertCount(24, $result['labels']);
        $this->assertSame(2, $result['values'][now()->hour]);
        $this->assertSame(2, array_sum($result['values']));
    }

    public function test_delta_calculations(): void
    {
        $this->assertSame(100.0, $this->service->delta(5, 0));
        $this->assertSame(0.0, $this->service->delta(0, 0));
        $this->assertSame(50.0, $this->service->delta(15, 10));
        $this->assertSame(-50.0, $this->service->delta(5, 10));
    }

    public function test_period_ranges_do_not_overlap_previous(): void
    {
        foreach (DashboardPeriod::cases() as $period) {
            [$from, $to] = $period->range();
            [$pFrom, $pTo] = $period->previousRange();

            $this->assertTrue($pTo->lt($from), "{$period->value} previous range must end before current range.");
        }
    }
}
