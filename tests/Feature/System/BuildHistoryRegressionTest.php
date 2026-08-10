<?php

namespace Tests\Feature\System;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Reports\SuppliersReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression suite for the 10-point targeted build-history audit.
 */
class BuildHistoryRegressionTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------ //
    // 1. SuppliersReport::rows() — dead $enquiries line removed           //
    // ------------------------------------------------------------------ //

    public function test_suppliers_report_rows_does_not_reference_supplier_enquiries_property(): void
    {
        $source = file_get_contents(
            app_path('Services/Reports/SuppliersReport.php')
        );

        $this->assertStringNotContainsString(
            'supplierEnquiries',
            $source,
            'SuppliersReport still references the dead $s->supplierEnquiries property.'
        );
    }

    public function test_suppliers_report_rows_runs_without_error_on_empty_db(): void
    {
        $report = new SuppliersReport();
        $result = $report->rows(now()->subMonth(), now());

        $this->assertIsArray($result);
    }

    // ------------------------------------------------------------------ //
    // 2. Customer::orders() relation exists                                //
    // ------------------------------------------------------------------ //

    public function test_customer_has_orders_relation(): void
    {
        $customer = Customer::factory()->create();

        // Relation must be callable and return a HasMany instance
        $relation = $customer->orders();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $relation
        );
    }

    // ------------------------------------------------------------------ //
    // 3. users columns exist in the correct migration order               //
    // ------------------------------------------------------------------ //

    public function test_users_table_has_all_required_enterprise_columns(): void
    {
        $required = [
            'customer_id', 'theme', 'is_active', 'notification_preferences',
            'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at',
            'last_login_at', 'last_login_ip', 'failed_login_attempts', 'locked_until',
            'password_changed_at',
        ];

        foreach ($required as $column) {
            $this->assertTrue(
                \Illuminate\Support\Facades\Schema::hasColumn('users', $column),
                "users table is missing column: {$column}"
            );
        }
    }

    // ------------------------------------------------------------------ //
    // 4 & 5. FK migration order: converted_order_id / appointments       //
    // ------------------------------------------------------------------ //

    public function test_quotations_table_has_converted_order_id_column(): void
    {
        $this->assertTrue(
            \Illuminate\Support\Facades\Schema::hasColumn('quotations', 'converted_order_id'),
            'quotations.converted_order_id column is missing (migration #26 may not have run).'
        );
    }

    public function test_appointments_table_has_sales_order_id_column(): void
    {
        $this->assertTrue(
            \Illuminate\Support\Facades\Schema::hasColumn('appointments', 'sales_order_id'),
            'appointments.sales_order_id column is missing (migration #51 may not have run).'
        );
    }

    // ------------------------------------------------------------------ //
    // 6. documents.destroy route + DocumentController::destroy            //
    // ------------------------------------------------------------------ //

    public function test_documents_destroy_route_exists(): void
    {
        $routes = collect(\Illuminate\Support\Facades\Route::getRoutes()->getRoutesByName());

        $this->assertArrayHasKey(
            'documents.destroy',
            $routes,
            'Route [documents.destroy] is not registered.'
        );
    }

    public function test_document_controller_has_destroy_method(): void
    {
        $this->assertTrue(
            method_exists(\App\Http\Controllers\DocumentController::class, 'destroy'),
            'DocumentController::destroy() method is missing.'
        );
    }

    // ------------------------------------------------------------------ //
    // 7. LaunchCampaignAction — no activity_note leftover                 //
    // ------------------------------------------------------------------ //

    public function test_launch_campaign_action_has_no_activity_note_calls(): void
    {
        $source = file_get_contents(
            app_path('Actions/WhatsApp/LaunchCampaignAction.php')
        );

        $this->assertStringNotContainsString(
            'activity_note',
            $source,
            'LaunchCampaignAction still contains a leftover activity_note() call.'
        );
    }

    // ------------------------------------------------------------------ //
    // 8. LeadScoringService namespace + score() method                    //
    // ------------------------------------------------------------------ //

    public function test_lead_scoring_service_exists_and_is_resolvable(): void
    {
        $this->assertTrue(
            class_exists(\App\Services\Ai\LeadScoringService::class),
            'App\Services\Ai\LeadScoringService class not found.'
        );

        $service = app(\App\Services\Ai\LeadScoringService::class);

        $this->assertTrue(
            method_exists($service, 'score'),
            'LeadScoringService::score() method is missing.'
        );
    }

    public function test_lead_scoring_service_returns_integer_between_0_and_100(): void
    {
        $lead = Lead::factory()->create([
            'source'           => 'website',
            'estimated_value'  => 5500,
        ]);

        $score = app(\App\Services\Ai\LeadScoringService::class)->score($lead);

        $this->assertIsInt($score);
        $this->assertGreaterThanOrEqual(0, $score);
        $this->assertLessThanOrEqual(100, $score);
    }

    // ------------------------------------------------------------------ //
    // 9. bootstrap/app.php ability / abilities middleware aliases         //
    // ------------------------------------------------------------------ //

    public function test_ability_and_abilities_middleware_aliases_are_registered(): void
    {
        $aliases = app('router')->getMiddleware();

        $this->assertArrayHasKey(
            'ability',
            $aliases,
            'Middleware alias [ability] not registered in bootstrap/app.php.'
        );

        $this->assertArrayHasKey(
            'abilities',
            $aliases,
            'Middleware alias [abilities] not registered in bootstrap/app.php.'
        );
    }

    // ------------------------------------------------------------------ //
    // 10. InvoiceMail::rendered() fallback + template lookup symmetry    //
    // ------------------------------------------------------------------ //

    public function test_invoice_mail_has_rendered_method(): void
    {
        $this->assertTrue(
            method_exists(\App\Mail\InvoiceMail::class, 'rendered') ||
            (new \ReflectionClass(\App\Mail\InvoiceMail::class))->hasMethod('rendered'),
            'InvoiceMail is missing the rendered() private method.'
        );
    }

    public function test_invoice_mail_falls_back_gracefully_when_no_template_exists(): void
    {
        $customer = Customer::factory()->create();
        $invoice  = \App\Models\Invoice::factory()->create(['customer_id' => $customer->id]);

        $mail = new \App\Mail\InvoiceMail($invoice, 'https://example.com/invoices/public/1');

        // Calling envelope() triggers rendered() which must NOT throw even when
        // the email_templates table has no 'invoice_cover' row.
        $envelope = $mail->envelope();

        $this->assertStringContainsString(
            $invoice->reference,
            $envelope->subject,
            'InvoiceMail fallback subject does not contain the invoice reference.'
        );
    }

    public function test_quotation_mail_falls_back_gracefully_when_no_template_exists(): void
    {
        $customer  = Customer::factory()->create();
        $quotation = \App\Models\Quotation::factory()->create(['customer_id' => $customer->id]);

        $mail = new \App\Mail\QuotationMail($quotation, 'https://example.com/quotations/public/1');

        $envelope = $mail->envelope();

        $this->assertStringContainsString(
            $quotation->reference,
            $envelope->subject,
            'QuotationMail fallback subject does not contain the quotation reference.'
        );
    }
}
