<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\ChapterMember;
use App\Models\Companies\CompaniesMaster;
use App\Models\User\UserMaster;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChapterController extends Controller
{
    public function index(Request $request)
    {
        $query = Chapter::withCount(['members', 'activeMembers']);
        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%")->orWhere('city', 'like', "%{$term}%"));
        }
        if ($request->filled('status')) $query->where('status', $request->status);
        $chapters = $query->orderBy('name')->paginate(20)->withQueryString();
        $summary = Chapter::selectRaw('COUNT(*) total, SUM(status = "active") active, SUM(status = "forming") forming')->first();
        echo $this->admin_after_login_layout('Chapters', 'chapters.index', compact('chapters', 'summary'));
    }

    public function create()
    {
        $chapter = new Chapter(['status' => 'forming']);
        echo $this->admin_after_login_layout('Create Chapter', 'chapters.form', compact('chapter'));
    }

    public function store(Request $request)
    {
        $chapter = Chapter::create($this->validatedChapter($request));
        return redirect()->route('admin.chapters.show', $chapter)->with('success', 'Chapter created successfully.');
    }

    public function show(Chapter $chapter)
    {
        $chapter->loadCount(['members', 'activeMembers']);
        $members = $chapter->members()->with(['user.userDetail', 'company.details'])->orderByRaw("FIELD(status, 'active', 'invited', 'inactive', 'left')")->orderBy('role')->paginate(30);
        $availableUsers = UserMaster::with('userDetail')->whereNotIn('um_id', $chapter->members()->pluck('user_id'))->orderBy('um_user_name')->get();
        $companies = CompaniesMaster::with('details')->get()->sortBy('cmp_name');
        echo $this->admin_after_login_layout($chapter->name, 'chapters.show', compact('chapter', 'members', 'availableUsers', 'companies'));
    }

    public function edit(Chapter $chapter)
    {
        echo $this->admin_after_login_layout('Edit Chapter', 'chapters.form', compact('chapter'));
    }

    public function update(Request $request, Chapter $chapter)
    {
        $chapter->update($this->validatedChapter($request, $chapter));
        return redirect()->route('admin.chapters.show', $chapter)->with('success', 'Chapter updated successfully.');
    }

    public function destroy(Chapter $chapter)
    {
        abort_if($chapter->members()->exists(), 422, 'Move or remove members before deleting this chapter.');
        $chapter->delete();
        return redirect()->route('admin.chapters.index')->with('success', 'Chapter deleted.');
    }

    public function addMember(Request $request, Chapter $chapter)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:user_master,um_id', Rule::unique('chapter_members')->where('chapter_id', $chapter->id)],
            'company_id' => ['nullable', 'exists:companies_master,cmp_id'],
            'role' => ['required', Rule::in($this->roles())],
            'status' => ['required', Rule::in(['invited', 'active', 'inactive', 'left'])],
            'joined_on' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $chapter->members()->create($data);
        return back()->with('success', 'Member added to chapter.');
    }

    public function updateMember(Request $request, Chapter $chapter, ChapterMember $member)
    {
        abort_unless($member->chapter_id === $chapter->id, 404);
        $data = $request->validate([
            'company_id' => ['nullable', 'exists:companies_master,cmp_id'],
            'role' => ['required', Rule::in($this->roles())],
            'status' => ['required', Rule::in(['invited', 'active', 'inactive', 'left'])],
            'joined_on' => ['required', 'date'],
            'left_on' => ['nullable', 'date', 'after_or_equal:joined_on'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        if ($data['status'] === 'left' && empty($data['left_on'])) $data['left_on'] = now()->toDateString();
        if ($data['status'] !== 'left') $data['left_on'] = null;
        $member->update($data);
        return back()->with('success', 'Chapter membership updated.');
    }

    public function removeMember(Chapter $chapter, ChapterMember $member)
    {
        abort_unless($member->chapter_id === $chapter->id, 404);
        $member->update(['status' => 'left', 'left_on' => now()->toDateString()]);
        return back()->with('success', 'Chapter membership ended. Its history has been retained.');
    }

    private function validatedChapter(Request $request, ?Chapter $chapter = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'alpha_dash', 'max:30', Rule::unique('chapters')->ignore($chapter?->id)],
            'city' => ['nullable', 'string', 'max:120'],
            'venue' => ['nullable', 'string', 'max:255'],
            'meeting_day' => ['nullable', Rule::in(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'])],
            'meeting_time' => ['nullable', 'date_format:H:i'],
            'established_on' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(['forming', 'active', 'paused', 'closed'])],
        ]);
    }

    private function roles(): array
    {
        return ['member', 'president', 'vice_president', 'secretary_treasurer', 'membership_committee', 'visitor_host'];
    }
}
