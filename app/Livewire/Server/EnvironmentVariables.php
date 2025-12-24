<?php

namespace App\Livewire\Server;

use App\Models\EnvironmentVariable;
use App\Models\Server;
use App\Traits\EnvironmentVariableProtection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class EnvironmentVariables extends Component
{
    use AuthorizesRequests, EnvironmentVariableProtection;

    public $server;

    protected $listeners = [
        'saveKey' => 'submit',
        'refreshEnvs',
        'environmentVariableDeleted' => 'refreshEnvs',
    ];

    public function mount(string $server_uuid)
    {
        $this->server = Server::ownedByCurrentTeam()->whereUuid($server_uuid)->firstOrFail();
    }

    public function instantSave()
    {
        try {
            $this->authorize('update', $this->server);
            $this->dispatch('success', 'Settings saved.');
        } catch (\Throwable $e) {
            return handleError($e, $this);
        }
    }

    public function render()
    {
        return view('livewire.server.environment-variables');
    }
}