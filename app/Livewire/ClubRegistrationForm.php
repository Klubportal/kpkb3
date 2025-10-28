<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Validate;
use App\Models\ClubRegistration;
use Illuminate\Support\Str;

class ClubRegistrationForm extends Component
{
    #[Validate('required|string|min:3|max:100')]
    public $club_name = '';

    #[Validate('required|string|min:3|max:50|regex:/^[a-z0-9-]+$/|unique:club_registrations,subdomain')]
    public $subdomain = '';

    #[Validate('required|email|max:255|unique:club_registrations,email')]
    public $email = '';

    #[Validate('required|string|min:2|max:100')]
    public $contact_person = '';

    #[Validate('nullable|string|max:20')]
    public $phone = '';

    #[Validate('required|in:kb,bm')]
    public $template = 'kb';

    #[Validate('accepted')]
    public $terms = false;

    public $submitted = false;

    // Auto-generate subdomain from club name
    public function updatedClubName($value)
    {
        if (empty($this->subdomain) || $this->subdomain === Str::slug($this->club_name)) {
            $this->subdomain = Str::slug($value);
        }
    }

    public function submit()
    {
        $validated = $this->validate();

        try {
            ClubRegistration::create([
                'club_name' => $validated['club_name'],
                'subdomain' => $validated['subdomain'],
                'email' => $validated['email'],
                'contact_person' => $validated['contact_person'],
                'phone' => $validated['phone'] ?? null,
                'template' => $validated['template'],
                'status' => 'pending',
            ]);

            $this->submitted = true;
            $this->reset(['club_name', 'subdomain', 'email', 'contact_person', 'phone', 'template', 'terms']);

            session()->flash('registration_success', true);

        } catch (\Exception $e) {
            $this->addError('form', 'Ein Fehler ist aufgetreten. Bitte versuche es später erneut.');
            \Log::error('Club registration error: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.club-registration-form');
    }
}
