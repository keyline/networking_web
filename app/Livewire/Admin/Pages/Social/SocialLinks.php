<?php

namespace App\Livewire\Admin\Pages\Social;

use App\Models\Social\SocialLinks as SocialLinksModel;
use Livewire\Component;
use Illuminate\Validation\ValidationException;

class SocialLinks extends Component
{
    public $facebook_link, $twitter_link, $instagram_link, $linkedin_link;
    public $successMessage;
    public $business_id;


    public function mount($business_id)
    {
        $this->business_id = $business_id;

        // Fetch existing social links for the given business ID
        $socialLinks = SocialLinksModel::where('cs_cmp_id', $this->business_id)->first();

        if ($socialLinks) {
            $this->facebook_link = $socialLinks->facebook_link;
            $this->twitter_link = $socialLinks->twitter_link;
            $this->instagram_link = $socialLinks->instagram_link;
            $this->linkedin_link = $socialLinks->linkedin_link;
        }
    }

    protected function rules()
    {
        return [
            'facebook_link' => [
                'nullable',
                'max:255',
                'regex:/^(?:https?:\/\/)?(?:www\.)?(mbasic\.facebook|m\.facebook|facebook|fb)\.(com|me)\/(?:(?:\w\.)*#!\/)?(?:pages\/)?(?:[\w\-\.]*\/)*([\w\-\.]*)$/i'
            ],
            'twitter_link' => [
                'nullable',
                'max:255',
                'regex:/^(?:https?:\/\/)?(?:www\.)?twitter\.com\/(?:#!\/)?(\w+)$/i'
            ],
            'instagram_link' => [
                'nullable',
                'max:255',
                'regex:/^(?:https?:\/\/)?(?:www\.)?instagram\.com\/([a-zA-Z0-9_\.]+)\/?$/i'
            ],
            'linkedin_link' => [
                'nullable',
                'max:255',
                'regex:/^(?:https?:\/\/)?(?:www\.)?linkedin\.com\/(in|company)\/[a-zA-Z0-9_-]+\/?$/i'
            ],
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }


    public function save()
    {
        try {
            $validated = $this->validate();

            $status =   SocialLinksModel::updateOrCreate([
                'cs_cmp_id' => $this->business_id,
            ], [
                'cs_cmp_id' =>  $this->business_id,
                'facebook_link' => $validated['facebook_link'],
                'twitter_link' => $validated['twitter_link'],
                'instagram_link' => $validated['instagram_link'],
                'linkedin_link' => $validated['linkedin_link']
            ]);

            if ($status)
                $this->dispatch('successMessage', 'Social Links Updated Successfully!');

            // Reset the input fields after submission
            // $this->reset(['facebook_link', 'twitter_link', 'instagram_link', 'linkedin_link']);
        } catch (ValidationException $e) {
            $this->dispatch('error', $e->validator->errors()->toArray());
        }
    }



    public function render()
    {
        return view('livewire.admin.pages.social.social-links');
    }
}
