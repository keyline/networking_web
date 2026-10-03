<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LegalPagesService;
use Illuminate\Http\Request;

/** Saves the Privacy Policy and Terms & Conditions shown on the website and app. */
class LegalPagesController extends Controller
{
    public function update(Request $request)
    {
        $rules = [];
        foreach (array_keys(LegalPagesService::PAGES) as $key) {
            $rules[$key . '_content'] = ['nullable', 'string', 'max:200000'];
        }
        $validated = $request->validate($rules);

        foreach (LegalPagesService::pages() as $key => $page) {
            $page->update([
                'page_content' => LegalPagesService::sanitize($validated[$key . '_content'] ?? ''),
                'status'       => 1,
                'updated_by'   => (int) $request->session()->get('user_id'),
            ]);
        }

        return redirect('admin/settings#tab13')->with('success_message', 'Privacy Policy and Terms & Conditions updated successfully.');
    }
}
