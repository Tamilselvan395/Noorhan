<?php

namespace App\Services\Communications;

use App\Models\EmailTemplate;

class TemplateRendererService
{
    /** @return array{subject: string, body: string} */
    public function render(EmailTemplate $template, array $context): array
    {
        $map = [];

        foreach ($this->flatten($context) as $key => $value) {
            $map['{{'.$key.'}}'] = (string) $value;
        }

        return [
            'subject' => strtr($template->subject, $map),
            'body' => strtr($template->body, $map),
        ];
    }

    /** Dot-notation flattening: ['customer' => ['name' => 'X']] → ['customer.name' => 'X'] */
    private function flatten(array $context, string $prefix = ''): array
    {
        $out = [];

        foreach ($context as $key => $value) {
            $full = $prefix ? "{$prefix}.{$key}" : $key;

            is_array($value)
                ? $out += $this->flatten($value, $full)
                : $out[$full] = $value;
        }

        return $out;
    }
}