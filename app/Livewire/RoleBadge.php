<?php

namespace App\Livewire;

use Livewire\Component;

class RoleBadge extends Component
{
    public $roles='';

    public function render()
    {
        foreach (auth()->user()->getRoleNames() as $role) {
            $this->roles .= __('role.'.$role) . " ";
        }
        return <<<'HTML'
        <span style="color: #DB8121">
            { {{$roles}} }
        </span>
        HTML;
    }
}
