<?php

namespace App\Livewire\Portal;

use App\Actions\Otozaar\CreateAppointmentAction;
use App\Models\Product;
use Illuminate\View\View;
use Livewire\Component;

class BookService extends Component
{
    public ?int $product_id = null;
    public string $scheduled_at = '';
    public string $vehicle_make = '';
    public string $vehicle_model = '';
    public string $vehicle_year = '';
    public string $plate = '';
    public string $notes = '';

    public function book(CreateAppointmentAction $action): void
    {
        $this->validate([
            'product_id' => ['required', 'exists:products,id'],
            'scheduled_at' => ['required', 'date', 'after:now'],
        ]);

        $service = Product::findOrFail($this->product_id);

        abort_unless($service->division === 'otozaar' && $service->is_active, 422);

        $action->execute([
            'customer_id' => auth()->user()->customer_id,
            'product_id' => $service->id,
            'scheduled_at' => $this->scheduled_at,
            'vehicle_make' => $this->vehicle_make ?: null,
            'vehicle_model' => $this->vehicle_model ?: null,
            'vehicle_year' => $this->vehicle_year ?: null,
            'plate' => $this->plate ?: null,
            'notes' => $this->notes ?: null,
            'status' => 'booked',
        ], auth()->user());

        $this->reset();

        $this->dispatch('notify', message: 'Service booked — see you soon!', type: 'success');
    }

    public function render(): View
    {
        return view('livewire.portal.book-service', [
            'services' => Product::active()->where('division', 'otozaar')->orderBy('name')->get(),
        ]);
    }
}