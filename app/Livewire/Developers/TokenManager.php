<?php

namespace App\Livewire\Developers;

use Illuminate\View\View;
use Livewire\Component;

class TokenManager extends Component
{
    public string $name = '';
    public bool $canRead = true;
    public bool $canWrite = false;
    public ?string $newToken = null;

    public function createToken(): void
    {
        $this->validate(['name' => 'required|string|max:80']);

        $abilities = array_filter(['read' => $this->canRead, 'write' => $this->canWrite]);

        abort_if($abilities === [], 422);

        $token = auth()->user()->createToken($this->name, array_keys($abilities));

        $this->newToken = $token->plainTextToken;
        $this->name = '';

        $this->dispatch('notify', message: 'Token created — copy it now.', type: 'success');
    }

    public function revoke(int $tokenId): void
    {
        auth()->user()->tokens()->where('id', $tokenId)->first()?->delete();

        $this->newToken = null;

        $this->dispatch('notify', message: 'Token revoked.', type: 'success');
    }

    public function render(): View
    {
        return view('livewire.developers.token-manager', [
            'tokens' => auth()->user()->tokens()->orderByDesc('id')->get(),
        ]);
    }
}