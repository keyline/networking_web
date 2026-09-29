<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Page;
use App\Models\Admin;
use Illuminate\Support\Str;
use Auth;
use Helper;

class PageController extends Controller
{
    public function __construct()
    {
        $this->data = array(
            'title'             => 'Page',
            'controller'        => 'PageController',
            'controller_route'  => 'page',
            'primary_key'       => 'id',
        );
    }
    /* list */
    public function list()
    {
        $data['module']                 = $this->data;
        $title                          = $this->data['title'] . ' List';
        $page_name                      = 'page.list';
        $data['rows']                   = Page::with('parent')->where('status', '!=', 3)->orderBy('nav_order')->orderBy('id', 'DESC')->get();
        $sessionData = Auth::guard('admin')->user();
        $data['admin'] = Admin::where('id', '=', $sessionData->id)->orderBy('id', 'DESC')->get();
        echo $this->admin_after_login_layout($title, $page_name, $data);
    }
    /* list */
    /* add */
    public function add(Request $request)
    {
        $data['module']           = $this->data;
        if ($request->isMethod('post')) {
            $validated = $this->validatePage($request);
            $sessionData = Auth::guard('admin')->user();
            $validated['created_by'] = $sessionData->id;
            $validated['company_id'] = $sessionData->company_id;
            Page::create($validated);

            return redirect("admin/{$this->data['controller_route']}/list")
                ->with('success_message', 'Page created successfully.');
        }
        $data['module']                 = $this->data;
        $title                          = $this->data['title'] . ' Add';
        $page_name                      = 'page.add-edit';
        $data['row']                    = [];
        $data['parentPages']            = Page::where('status', '!=', 3)->orderBy('page_name')->get();
        echo $this->admin_after_login_layout($title, $page_name, $data);
    }
    /* add */
    /* edit */
    public function edit(Request $request, $id)
    {
        $data['module']                 = $this->data;
        $id                             = Helper::decoded($id);
        $title                          = $this->data['title'] . ' Update';
        $page_name                      = 'page.add-edit';
        $data['row']                    = Page::where($this->data['primary_key'], '=', $id)->firstOrFail();
        $data['parentPages']            = Page::where('status', '!=', 3)->where('id', '!=', $id)->orderBy('page_name')->get();
        if ($request->isMethod('post')) {
            $validated = $this->validatePage($request, $id);
            $validated['updated_by'] = Auth::guard('admin')->id();
            $data['row']->update($validated);

            return redirect("admin/{$this->data['controller_route']}/list")
                ->with('success_message', 'Page updated successfully.');
        }

        echo $this->admin_after_login_layout($title, $page_name, $data);
    }
    /* edit */
    /* delete */
    public function delete(Request $request, $id)
    {
        $id                             = Helper::decoded($id);
        $fields = [
            'status'             => 3
        ];
        Page::where($this->data['primary_key'], '=', $id)->update($fields);
        return redirect("admin/" . $this->data['controller_route'] . "/list")->with('success_message', $this->data['title'] . ' Deleted Successfully !!!');
    }
    /* delete */
    /* change status */
    public function change_status(Request $request, $id)
    {
        $id                             = Helper::decoded($id);
        $model                          = Page::find($id);
        if ($model->status == 1) {
            $model->status  = 0;
            $msg            = 'Deactivated';
        } else {
            $model->status  = 1;
            $msg            = 'Activated';
        }
        $model->save();
        return redirect("admin/" . $this->data['controller_route'] . "/list")->with('success_message', $this->data['title'] . ' ' . $msg . ' Successfully !!!');
    }
    /* change status */

    private function validatePage(Request $request, ?int $id = null): array
    {
        $slug = Str::slug($request->input('page_slug') ?: $request->input('page_name'));
        $request->merge(['page_slug' => $slug]);

        $validated = $request->validate([
            'page_name' => ['required', 'string', 'max:255'],
            'page_slug' => ['required', 'string', 'max:255', Rule::unique('pages', 'page_slug')->ignore($id)],
            'page_content' => ['required', 'string'],
            'parent_id' => ['nullable', 'integer', Rule::exists('pages', 'id')->where(fn ($query) => $query->where('status', '!=', 3))],
            'nav_label' => ['nullable', 'string', 'max:100'],
            'nav_location' => ['required', Rule::in(['none', 'header', 'footer', 'both'])],
            'nav_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'template' => ['required', Rule::in(['default', 'full-width', 'landing'])],
            'meta_title' => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:170'],
            'published_at' => ['nullable', 'date'],
        ]);

        if ($id && (int) ($validated['parent_id'] ?? 0) === $id) {
            abort(422, 'A page cannot be its own parent.');
        }

        $validated['parent_id'] = $validated['parent_id'] ?: null;
        $validated['nav_label'] = $validated['nav_label'] ?: $validated['page_name'];
        $validated['status'] = $request->input('save_action') === 'publish' ? 1 : 0;
        $validated['published_at'] = $validated['status'] === 1
            ? ($validated['published_at'] ?: now())
            : null;

        return $validated;
    }
}
