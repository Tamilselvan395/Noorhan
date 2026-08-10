<?php

namespace App\Livewire\Divisions;

use App\Actions\Otozaar\AdvanceAppointmentAction;
use App\Actions\Otozaar\CreateAppointmentAction;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Component;
use RuntimeException;

class OtozaarPanel extends Component
{
    public bool $formOpen = false;

    public ?int $customer_id = null;
    public ?int $product_id = null;
    public ?int $assigned_to = null;
    public string $scheduled_at = '';
    public string $vehicle_make = '';
    public string $vehicle_model = '';
    public string $vehicle_year = '';
    public string $plate = '';
    public string $price_estimate = '';
    public string $notes = '';

    public function openForm(): void
    {
        $this->reset(['customer_id', 'product_id', 'assigned_to', 'scheduled_at', 'vehicle_make', 'vehicle_model', 'vehicle_year', 'plate', 'price_estimate', 'notes']);
        $this->formOpen = true;
    }

    public function book(CreateAppointmentAction $action): void
    {
        $this->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'product_id' => ['required', 'exists:products,id'],
            'scheduled_at' => ['required', 'date'],
        ]);

        $action->execute([
            'customer_id' => $this->customer_id,
            'product_id' => $this->product_id,
            'scheduled_at' => $this->scheduled_at,
            'assigned_to' => $this->assigned_to,
            'vehicle_make' => $this->vehicle_make ?: null,
            'vehicle_model' => $this->vehicle_model ?: null,
            'vehicle_year' => $this->vehicle_year ?: null,
            'plate' => $this->plate ?: null,
            'price_estimate' => $this->price_estimate ?: null,
            'notes' => $this->notes ?: null,
            'status' => 'booked',
        ], auth()->user());

        $this->formOpen = false;
        $this->dispatch('notify', message: 'Appointment booked.', type: 'success');
    }

    public function advance(int $id, string $status, AdvanceAppointmentAction $action): void
    {
        $appointment = Appointment::findOrFail($id);

        try {
            $action->execute($appointment, AppointmentStatus::from($status), auth()->user());
            $this->dispatch('notify', message: "Appointment {$appointment->reference} → ".AppointmentStatus::from($status)->label(), type: 'success');
        } catch (RuntimeException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function render(AdvanceAppointmentAction $action): View
    {
        $services = Product::active()->where('division', 'otozaar')->orderBy('name')->get();

        return view('livewire.divisions.otozaar-panel', [
            'services' => $services,
            'today' => Appointment::with(['customer', 'service', 'technician'])
                ->whereDate('scheduled_at', today())->orderBy('scheduled_at')->get(),
            'upcoming' => Appointment::with(['customer', 'service'])
                ->where('scheduled_at', '>', now())->where('status', 'booked')
                ->orderBy('scheduled_at')->limit(8)->get(),
            'capacity' => Appointment::whereDate('scheduled_at', today())
                ->whereNotIn('status', ['cancelled', 'no_show'])
                ->get()
                ->groupBy(fn ($a) => $a->technician?->name ?? 'Unassigned')
                ->map(fn ($group) => $group->sum('estimated_minutes'))
                ->sortDesc(),
            'transitions' => $action->transitions(),
            'customers' => Customer::active()->orderBy('name')->limit(300)->get(),
            'users' => User::orderBy('name')->get(),
        ]);
    }
}