<?php

namespace App\Livewire\Team;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class AdminView extends Component
{
    use WithPagination;

    public string $search = '';

    public function mount()
    {
        if (! isInstanceAdmin()) {
            return redirect()->route('dashboard');
        }
    }

    public function updatedSearch(): void
    {
        if (! isInstanceAdmin()) {
            return;
        }

        $this->resetPage();
    }

    public function submitSearch(): void
    {
        if (! isInstanceAdmin()) {
            return;
        }

        $this->resetPage();
    }

    public function delete($id, $password, $selectedActions = [])
    {
        if (! isInstanceAdmin()) {
            return redirect()->route('dashboard');
        }

        if (! verifyPasswordConfirmation($password, $this)) {
            return 'The provided password is incorrect.';
        }

        if (! auth()->user()->isInstanceAdmin()) {
            return $this->dispatch('error', 'You are not authorized to delete users');
        }

        $user = User::find($id);
        if (! $user) {
            return $this->dispatch('error', 'User not found');
        }

        try {
            $user->delete();
            $this->resetPage();

            return true;
        } catch (\Exception $e) {
            return $this->dispatch('error', $e->getMessage());
        }
    }

    public function render()
    {
        $search = trim($this->search);
        $users = User::query()
            ->where('id', '!=', auth()->id())
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->orderBy('email')
            ->paginate(10);

        return view('livewire.team.admin-view', [
            'users' => $users,
        ]);
    }
}
