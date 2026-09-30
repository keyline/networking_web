<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomepageSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class HomepageController extends Controller
{
    public function index()
    {
        $sections = HomepageSection::withCount('items')->orderBy('sort_order')->orderBy('id')->get();
        echo $this->admin_after_login_layout('Homepage CMS', 'homepage.index', compact('sections'));
    }

    public function create(Request $request)
    {
        $section = new HomepageSection(['type' => $request->query('type', 'content'), 'is_active' => true]);
        echo $this->admin_after_login_layout('Add homepage section', 'homepage.form', $this->formData($section));
    }

    public function store(Request $request)
    {
        $section = new HomepageSection();
        $this->save($request, $section);
        return redirect()->route('admin.homepage.index')->with('success', 'Homepage section added.');
    }

    public function edit(HomepageSection $homepage)
    {
        $homepage->load('items');
        echo $this->admin_after_login_layout('Edit homepage section', 'homepage.form', $this->formData($homepage));
    }

    public function update(Request $request, HomepageSection $homepage)
    {
        $this->save($request, $homepage);
        return redirect()->route('admin.homepage.index')->with('success', 'Homepage section updated.');
    }

    public function destroy(HomepageSection $homepage)
    {
        $homepage->delete();
        return back()->with('success', 'Homepage section removed.');
    }

    public function move(HomepageSection $homepage, string $direction)
    {
        abort_unless(in_array($direction, ['up', 'down'], true), 404);
        $operator = $direction === 'up' ? '<' : '>';
        $order = $direction === 'up' ? 'desc' : 'asc';
        $other = HomepageSection::where('sort_order', $operator, $homepage->sort_order)->orderBy('sort_order', $order)->first();
        if ($other) {
            [$homepageOrder, $otherOrder] = [$homepage->sort_order, $other->sort_order];
            DB::transaction(function () use ($homepage, $other, $homepageOrder, $otherOrder) {
                $homepage->update(['sort_order' => $otherOrder]);
                $other->update(['sort_order' => $homepageOrder]);
            });
        }
        return back();
    }

    private function save(Request $request, HomepageSection $section): void
    {
        $types = array_keys($this->types());
        $data = $request->validate([
            'type' => ['required', Rule::in($types)],
            'name' => ['required', 'string', 'max:120'],
            'eyebrow' => ['nullable', 'string', 'max:120'],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:5000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
            'settings.button_label' => ['nullable', 'string', 'max:80'],
            'settings.button_url' => ['nullable', 'string', 'max:255'],
            'settings.secondary_label' => ['nullable', 'string', 'max:80'],
            'settings.secondary_url' => ['nullable', 'string', 'max:255'],
            'settings.limit' => ['nullable', 'integer', 'min:1', 'max:12'],
            'items' => ['nullable', 'array', 'max:20'],
            'items.*.title' => ['nullable', 'string', 'max:255'],
            'items.*.subtitle' => ['nullable', 'string', 'max:255'],
            'items.*.body' => ['nullable', 'string', 'max:2000'],
            'items.*.link_label' => ['nullable', 'string', 'max:80'],
            'items.*.link_url' => ['nullable', 'string', 'max:255'],
            'items.*.existing_image' => ['nullable', 'string', 'max:255'],
            'items.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'items.*.meta_role' => ['nullable', 'string', 'max:150'],
        ]);

        DB::transaction(function () use ($request, $section, $data) {
            $section->fill([
                'type' => $data['type'],
                'name' => $data['name'],
                'eyebrow' => $data['eyebrow'] ?? null,
                'title' => $data['title'] ?? null,
                'body' => $data['body'] ?? null,
                'settings' => array_filter($data['settings'] ?? [], fn ($value) => $value !== null && $value !== ''),
                'sort_order' => $data['sort_order'] ?? ($section->exists ? $section->sort_order : ((int) HomepageSection::max('sort_order') + 10)),
                'is_active' => $request->boolean('is_active'),
            ])->save();

            $section->items()->delete();
            foreach ($data['items'] ?? [] as $index => $item) {
                if (!collect($item)->except(['existing_image'])->filter()->count() && empty($item['existing_image'])) continue;
                $image = $item['existing_image'] ?? null;
                if ($request->hasFile("items.$index.image")) {
                    $file = $request->file("items.$index.image");
                    $name = uniqid('home_', true).'.'.$file->extension();
                    File::ensureDirectoryExists(public_path('uploads/homepage'));
                    $file->move(public_path('uploads/homepage'), $name);
                    $image = 'uploads/homepage/'.$name;
                }
                $section->items()->create([
                    'title' => $item['title'] ?? null,
                    'subtitle' => $item['subtitle'] ?? null,
                    'body' => $item['body'] ?? null,
                    'image' => $image,
                    'link_label' => $item['link_label'] ?? null,
                    'link_url' => $item['link_url'] ?? null,
                    'meta' => array_filter(['role' => $item['meta_role'] ?? null]),
                    'sort_order' => $index,
                    'is_active' => true,
                ]);
            }
        });
    }

    private function formData(HomepageSection $section): array
    {
        return ['section' => $section, 'types' => $this->types()];
    }

    private function types(): array
    {
        return [
            'hero' => 'Header carousel',
            'steps' => 'Step-by-step process',
            'features' => 'Feature cards',
            'content' => 'Text and image content',
            'gallery' => 'Image gallery',
            'testimonials' => 'Testimonials',
            'events' => 'Upcoming events',
            'cta' => 'Call to action',
        ];
    }
}
