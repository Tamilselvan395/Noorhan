<?php

namespace Tests\Feature\Reports;

use App\Actions\Invoices\CreateInvoiceFromOrderAction;
use App\Actions\Invoices\SendInvoiceAction;
use App\Actions\SalesOrders\CreateSalesOrderAction;
use App\Enums\CommunicationChannel;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\MarketingCampaign;
use App\Models\User;
use App\Services\Reports\AiAccuracyReport;
use App\Services\Reports\LeadSourcesReport;
use App\Services\Reports\OutstandingReport;
use App\Services\Reports\ReportRegistry;
use App\Services\Reports\SalesReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_registry_contains_all_twelve_reports(): void
    {
        $registry = app(ReportRegistry::class);

        $keys = ['sales','revenue','profit','marketing','lead_sources','campaign_performance',
            'products','suppliers','payments','outstanding','employees','ai_accuracy'];

        foreach ($keys as $key) {
            $this->assertNotNull($registry->resolve($key), "Missing report: {$key}");
        }

        $this->assertCount(12, $registry->grouped() ? collect($registry->grouped())->flatten() : []);
    }

    public function test_sales_report_lists_orders_and_summary(): void
    {
        $customer = Customer::factory()->create();

        app(CreateSalesOrderAction::class)->execute([
            'customer_id' => $customer->id, 'division' => 'automotive', 'status' => 'confirmed', 'tax_rate' => 5,
        ], [['product_id' => null, 'description' => 'X', 'quantity' => 1, 'unit_price' => 200, 'cost_price' => 100, 'discount_percent' => 0]], $this->user);

        $report = app(SalesReport::class);
        $rows = $report->rows(now()->startOfMonth(), now());
        $summary = $report->summary(now()->startOfMonth(), now());

        $this->assertCount(1, $rows);
        $this->assertSame('SO-00001', $rows[0][0]);
        $this->assertArrayHasKey('Orders', $summary);
    }

    public function test_outstanding_report_flags_overdue_invoices(): void
    {
        $customer = Customer::factory()->create();

        $order = app(CreateSalesOrderAction::class)->execute([
            'customer_id' => $customer->id, 'division' => 'automotive', 'status' => 'confirmed', 'tax_rate' => 5,
        ], [['product_id' => null, 'description' => 'X', 'quantity' => 1, 'unit_price' => 100, 'cost_price' => 50, 'discount_percent' => 0]], $this->user);

        $invoice = app(CreateInvoiceFromOrderAction::class)->execute($order);
        $invoice->update(['status' => 'sent', 'due_date' => now()->subDays(10)]);

        $rows = app(OutstandingReport::class)->rows(now()->startOfYear(), now());

        $this->assertCount(1, $rows);
        $this->assertSame(10, (int) $rows[0][3]); // days overdue
    }

    public function test_lead_sources_report_aggregates(): void
    {
        Lead::factory()->count(2)->create(['source' => 'exhibition']);

        $rows = app(LeadSourcesReport::class)->rows(now()->startOfYear(), now());

        $this->assertSame('Exhibition', $rows[0][0]);
        $this->assertSame(2, $rows[0][1]);
    }

    public function test_csv_export_streams_headers_and_rows(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('reports.export', ['key' => 'sales', 'from' => now()->startOfMonth()->toDateString(), 'to' => now()->toDateString()]));

        $response->assertOk();
        $response->assertDownload(str_contains($response->headers->get('content-disposition'), 'sales_') ? $response->headers->get('filename') : 'sales.csv');
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
    }

    public function test_report_center_renders(): void
    {
        $this->actingAs($this->user)->get(route('reports.index'))->assertOk()->assertSee('Report Center');
    }

    public function test_ai_accuracy_report_reads_routing_logs(): void
    {
        $lead = Lead::factory()->create();

        \App\Models\LeadRoutingLog::create([
            'lead_id' => $lead->id,
            'outcome' => 'ai_recommendation',
            'classification' => ['confidence' => 0.9],
        ]);

        $summary = app(AiAccuracyReport::class)->summary(now()->startOfYear(), now());

        $this->assertSame('1', $summary['AI Recommendations']);
    }
}