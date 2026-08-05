<?php

namespace Tests\Unit\Dashboard;

use App\Services\Dashboard\WidgetRegistry;
use App\Services\Dashboard\Widgets\ActiveNowWidget;
use App\Services\Dashboard\Widgets\UsersWidget;
use Tests\TestCase;

class WidgetRegistryTest extends TestCase
{
    public function test_widgets_resolve_sorted_by_sort_order(): void
    {
        $registry = new WidgetRegistry();
        $registry->register(ActiveNowWidget::class); // order 40
        $registry->register(UsersWidget::class);    // order 10

        $widgets = $registry->widgets();

        $this->assertSame('users', $widgets->first()->key());
        $this->assertSame('active_now', $widgets->last()->key());
    }

    public function test_invalid_widget_is_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new WidgetRegistry())->register(\stdClass::class);
    }
}