<?php

namespace App\Actions\Otozaar;

use App\Actions\SalesOrders\CreateSalesOrderAction;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\User;
use RuntimeException;

class AdvanceAppointmentAction
{
    public function __construct(private CreateSalesOrderAction $createOrder) {}

    /** @return array<string, array<int, AppointmentStatus>> */
    public function transitions(): array
    {
        return [
            AppointmentStatus::Booked->value => [AppointmentStatus::InProgress, AppointmentStatus::Cancelled, AppointmentStatus::NoShow],
            AppointmentStatus::InProgress->value => [AppointmentStatus::Completed],
            AppointmentStatus::Completed->value => [],
            AppointmentStatus::Cancelled->value => [],
            AppointmentStatus::NoShow->value => [],
        ];
    }

    public function execute(Appointment $appointment, AppointmentStatus $to, ?User $actor = null): void
    {
        $from = $appointment->status();

        if (! in_array($to, $this->transitions()[$from->value] ?? [], true)) {
            throw new RuntimeException("Invalid appointment move: {$from->label()} → {$to->label()}.");
        }

        $appointment->update([
            'status' => $to->value,
            'completed_at' => $to === AppointmentStatus::Completed ? now() : null,
        ]);

        // Completion converts the service into a Sales Order at the agreed price.
        if ($to === AppointmentStatus::Completed) {
            $service = $appointment->service;

            $order = $this->createOrder->execute([
                'customer_id' => $appointment->customer_id,
                'division' => 'otozaar',
                'status' => 'confirmed',
                'tax_rate' => 5,
                'notes' => "Service appointment {$appointment->reference} · {$appointment->vehicle()}",
            ], [[
                'product_id' => $service->id,
                'description' => $service->name,
                'quantity' => 1,
                'unit_price' => (float) ($appointment->price_estimate ?? $service->sale_price),
                'cost_price' => (float) $service->cost_price,
                'discount_percent' => 0,
            ]], $actor);

            $appointment->update(['sales_order_id' => $order->id]);
        }

        $appointment->customer?->logActivity("appointment {$appointment->reference} moved to {$to->label()}");
    }
}