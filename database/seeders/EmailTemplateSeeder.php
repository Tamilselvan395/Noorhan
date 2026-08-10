<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Welcome Email', 'key' => 'welcome_email', 'category' => 'onboarding',
                'subject' => 'Welcome to {{company.name}}, {{customer.name}}!',
                'body' => "Hi {{customer.name}},\n\nThank you for joining {{company.name}}. Your account manager will reach out shortly.\n\n— {{company.name}}",
            ],
            [
                'name' => 'Lead Follow-up', 'key' => 'lead_follow_up', 'category' => 'sales',
                'subject' => 'Following up on your enquiry — {{company.name}}',
                'body' => "Hi {{lead.name}},\n\nI'm following up on your recent enquiry. Do you have any questions I can help with?\n\nBest regards,\n{{user.name}}",
            ],
            [
                'name' => 'Quotation Cover', 'key' => 'quotation_cover', 'category' => 'sales',
                'subject' => 'Quotation {{quotation.reference}} from {{company.name}}',
                'body' => "Dear {{customer.name}},\n\nPlease find your quotation {{quotation.reference}} totalling {{quotation.total}}, valid until {{quotation.valid_until}}.\n\nView & accept: {{link}}\n\n— {{company.name}}",
            ],
            [
                'name' => 'Invoice Cover', 'key' => 'invoice_cover', 'category' => 'finance',
                'subject' => 'Invoice {{invoice.reference}} from {{company.name}}',
                'body' => "Dear {{customer.name}},\n\nYour invoice {{invoice.reference}} for {{invoice.total}} is now due ({{invoice.due_date}}).\n\nView: {{link}}\n\n— {{company.name}}",
            ],
            [
                'name' => 'Dormant Win-back', 'key' => 'dormant_winback', 'category' => 'marketing',
                'subject' => 'We miss you, {{customer.name}} — a special offer inside',
                'body' => "Hi {{customer.name}},\n\nIt's been a while! Enjoy priority pricing on your next order this month.\n\n— {{company.name}}\n\n{{unsubscribe_url}}",
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(['key' => $template['key']], $template);
        }
    }
}