<?php

namespace App\Livewire;

use Livewire\Component;

class RoleBadge extends Component
{
    public $roles='ss';

    public function mount()
    {
        $this->roles = auth()->user()->getRoleNames()->implode('name',', ');
    }
    public function render()
    {
        return <<<'HTML'
        <span style="color: #DB8121">
            { {{__('role.'.$roles)}} }
        </span>
        HTML;
    }
}
