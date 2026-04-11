<?php

namespace App\Livewire\Admin\Management\Classroom;

use App\Models\Classroom;
use App\Models\ClassroomSubject;
use App\Models\Subject;
use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class Action extends Component
{
    public ?int $selectedClassroomId = null;

    public ?string $selectedClassroomName = null;

    public ?int $editingClassroomId = null;

    public ?int $deletingClassroomId = null;

    public ?int $editingAssignmentId = null;

    public ?int $deletingAssignmentId = null;

    public string $name = '';

    public string $code = '';

    public string $description = '';

    public ?string $start_date = null;

    public ?string $end_date = null;

    public string $status = 'open';

    public ?int $subject_id = null;

    public string $youthSearch = '';

    public string $assignment_name = '';

    public bool $isAssignmentModalOpen = false;

    public bool $isYouthModalOpen = false;

    /**
     * @var array<int, int|string>
     */
    public array $teacher_ids = [];

    /**
     * @var array<int, int|string>
     */
    public array $youth_ids = [];

    public string $assignment_status = 'active';

    public function selectAllYouths(): void
    {
        $this->youth_ids = $this->availableYouths
            ->pluck('id')
            ->map(fn (int $id): string => (string) $id)
            ->all();
    }

    public function clearYouthSelection(): void
    {
        $this->youth_ids = [];
    }

    public function mount(?int $selectedClassroomId = null): void
    {
        $this->selectedClassroomId = $selectedClassroomId;
        $this->resetForm();
        $this->resetAssignmentForm();
    }

    public function updatedSelectedClassroomId(?int $value): void
    {
        $this->selectedClassroomId = $value;
        $this->selectedClassroomName = null;
    }

    #[On('classroom-selected')]
    public function syncSelectedClassroom(mixed $id = null): void
    {
        $resolvedId = is_array($id) ? ($id['id'] ?? null) : $id;

        if ($resolvedId === null || $resolvedId === '') {
            $this->selectedClassroomId = null;
            $this->selectedClassroomName = null;

            return;
        }

        if (! is_int($resolvedId) && ! ctype_digit((string) $resolvedId)) {
            return;
        }

        $this->selectedClassroomId = (int) $resolvedId;
        $this->selectedClassroomName = null;
    }

    #[On('classroom-data-changed')]
    public function handleClassroomDataChanged(mixed $selectedClassroomId = null): void
    {
        $resolvedId = is_array($selectedClassroomId)
            ? ($selectedClassroomId['selectedClassroomId'] ?? null)
            : $selectedClassroomId;

        if ($resolvedId === null || $resolvedId === '') {
            $this->selectedClassroomId = null;
            $this->selectedClassroomName = null;

            return;
        }

        if (! is_int($resolvedId) && ! ctype_digit((string) $resolvedId)) {
            return;
        }

        $this->selectedClassroomId = (int) $resolvedId;
        $this->selectedClassroomName = null;
    }

    #[On('open-create-classroom-modal')]
    public function openCreateModal(): void
    {
        $this->editingClassroomId = null;
        $this->resetForm();

        Flux::modal('showFormModal')->show();
    }

    #[On('open-edit-classroom-modal')]
    public function openEditModal($classroomId): void
    {
        if (! is_int($classroomId) && ! ctype_digit((string) $classroomId)) {
            return;
        }

        $classroomId = (int) $classroomId;

        if ($classroomId === null) {
            return;
        }

        $classroom = Classroom::query()->findOrFail($classroomId);

        $this->selectedClassroomId = $classroom->id;
        $this->selectedClassroomName = $classroom->name;
        $this->editingClassroomId = $classroom->id;
        $this->name = $classroom->name;
        $this->code = $classroom->code ?? '';
        $this->description = $classroom->description ?? '';
        $this->start_date = $classroom->start_date?->format('Y-m-d');
        $this->end_date = $classroom->end_date?->format('Y-m-d');
        $this->status = $classroom->status;
        $this->resetErrorBag();
        $this->resetValidation();

        Flux::modal('showFormModal')->show();
    }

    #[On('open-delete-classroom-modal')]
    public function openDeleteModal(mixed $params = null): void
    {
        $classroomId = $this->resolveId($params);

        if ($classroomId === null) {
            return;
        }

        $this->deletingClassroomId = $classroomId;
        $this->resetErrorBag();
        $this->resetValidation();

        Flux::modal('showDeleteModal')->show();
    }

    #[On('open-create-assignment-modal')]
    public function openCreateAssignmentModal(): void
    {
        if ($this->selectedClassroomId === null) {
            Flux::toast(variant: 'warning', text: __('Hãy tạo lớp học trước khi thêm môn học.'));

            return;
        }

        if (! Subject::query()->exists()) {
            Flux::toast(variant: 'warning', text: __('Hãy tạo môn học trước khi gán vào lớp.'));

            return;
        }

        if (! User::query()->whereHas('roles', fn ($query) => $query->where('name', 'giáo viên'))->exists()) {
            Flux::toast(variant: 'warning', text: __('Hãy tạo hoặc gán role giáo viên trước khi phân công môn học.'));

            return;
        }

        $this->editingAssignmentId = null;
        $this->isAssignmentModalOpen = true;
        $this->selectedClassroomName = Classroom::query()->whereKey($this->selectedClassroomId)->value('name');
        $this->resetAssignmentForm();

        Flux::modal('showAssignmentModal')->show();
    }

    #[On('open-edit-assignment-modal')]
    public function openEditAssignmentModal(mixed $params = null): void
    {
        $assignmentId = $this->resolveId($params);

        if ($assignmentId === null) {
            return;
        }

        $assignment = ClassroomSubject::query()
            ->with(['classroom:id,name', 'subject', 'teachers'])
            ->findOrFail($assignmentId);

        $this->selectedClassroomId = $assignment->classroom_id;
        $this->selectedClassroomName = $assignment->classroom?->name;
        $this->editingAssignmentId = $assignment->id;
        $this->isAssignmentModalOpen = true;
        $this->subject_id = $assignment->subject_id;
        $this->teacher_ids = $assignment->teachers
            ->pluck('id')
            ->map(fn (int $id): string => (string) $id)
            ->all();
        $this->assignment_status = $assignment->status;
        $this->resetErrorBag();
        $this->resetValidation();

        Flux::modal('showAssignmentModal')->show();
    }

    #[On('open-delete-assignment-modal')]
    public function openDeleteAssignmentModal(mixed $params = null): void
    {
        $assignmentId = $this->resolveId($params);

        if ($assignmentId === null) {
            return;
        }

        $assignment = ClassroomSubject::query()->findOrFail($assignmentId);

        $this->selectedClassroomId = $assignment->classroom_id;
        $this->deletingAssignmentId = $assignmentId;
        $this->resetErrorBag();
        $this->resetValidation();

        Flux::modal('showAssignmentDeleteModal')->show();
    }

    #[On('open-youth-modal')]
    public function openYouthModal(): void
    {
        $classroomId = $this->selectedClassroomId;

        if ($classroomId === null) {
            Flux::toast(variant: 'warning', text: __('Vui lòng chọn lớp học trước.'));

            return;
        }

        $this->youth_ids = Classroom::query()
            ->findOrFail($classroomId)
            ->youths()
            ->pluck('id')
            ->map(fn (int $id): string => (string) $id)
            ->all();
        $this->selectedClassroomName = Classroom::query()->whereKey($classroomId)->value('name');
        $this->isYouthModalOpen = true;
        $this->resetErrorBag();
        $this->resetValidation();

        Flux::modal('showYouthModal')->show();
    }

    public function saveClassroom(): void
    {
        $isEditing = $this->editingClassroomId !== null;

        $this->code = Str::upper($this->code);

        $validated = $this->validate(
            $this->editingClassroomId === null
                ? $this->createRules()
                : $this->updateRules($this->editingClassroomId),
        );

        if (blank($validated['code'])) {
            $validated['code'] = null;
        }

        if ($isEditing) {
            $classroom = Classroom::query()->findOrFail($this->editingClassroomId);
            $classroom->update($validated);
        } else {
            $classroom = Classroom::query()->create($validated);
        }

        $this->selectedClassroomId = $classroom->id;
        $this->selectedClassroomName = $classroom->name;
        Flux::modal('showFormModal')->close();
        $this->editingClassroomId = null;
        $this->resetForm();

        $this->dispatch('classroom-data-changed', selectedClassroomId: $classroom->id);

        Flux::toast(
            variant: 'success',
            text: $isEditing ? __('Đã cập nhật lớp học.') : __('Đã tạo lớp học mới.'),
        );
    }

    public function deleteClassroom(): void
    {
        $classroom = Classroom::query()->findOrFail($this->deletingClassroomId);
        $selectedClassroomId = $classroom->id === $this->selectedClassroomId ? null : $this->selectedClassroomId;
        $classroom->delete();

        Flux::modal('showDeleteModal')->close();
        $this->deletingClassroomId = null;
        $this->selectedClassroomId = $selectedClassroomId;
        $this->selectedClassroomName = null;

        $this->dispatch('classroom-data-changed', selectedClassroomId: $selectedClassroomId);

        Flux::toast(variant: 'success', text: __('Đã xoá lớp học.'));
    }

    public function saveAssignment(): void
    {
        $classroomId = $this->selectedClassroomId;

        if ($classroomId === null) {
            Flux::toast(variant: 'warning', text: __('Vui lòng chọn lớp học trước.'));

            return;
        }

        $isEditing = $this->editingAssignmentId !== null;

        $validated = $this->validate(
            $this->editingAssignmentId === null
                ? $this->createAssignmentRules($classroomId)
                : $this->updateAssignmentRules($classroomId, $this->editingAssignmentId),
        );

        $teacherIds = collect($validated['teacher_ids'])
            ->map(fn (int|string $teacherId): int => (int) $teacherId)
            ->unique()
            ->values()
            ->all();

        $teacherCount = User::query()
            ->whereKey($teacherIds)
            ->whereHas('roles', fn ($query) => $query->where('name', 'giáo viên'))
            ->count();

        if ($teacherCount !== count($teacherIds)) {
            $this->addError('teacher_ids', __('Vui lòng chỉ chọn tài khoản giáo viên.'));

            return;
        }

        if ($isEditing) {
            $assignment = ClassroomSubject::query()->findOrFail($this->editingAssignmentId);
            $assignment->update([
                'subject_id' => $validated['subject_id'],
                'status' => $validated['assignment_status'],
            ]);
        } else {
            $assignment = ClassroomSubject::query()->create([
                'classroom_id' => $classroomId,
                'subject_id' => $validated['subject_id'],
                'status' => $validated['assignment_status'],
            ]);
        }

        $assignment->teachers()->sync($teacherIds);

        Flux::modal('showAssignmentModal')->close();
        $this->editingAssignmentId = null;
        $this->resetAssignmentForm();

        $this->dispatch('classroom-data-changed', selectedClassroomId: $classroomId);

        Flux::toast(
            variant: 'success',
            text: $isEditing ? __('Đã cập nhật phân công môn học.') : __('Đã thêm môn học vào lớp.'),
        );
    }

    public function deleteAssignment(): void
    {
        $assignment = ClassroomSubject::query()->findOrFail($this->deletingAssignmentId);
        $classroomId = $assignment->classroom_id;
        $assignment->delete();

        Flux::modal('showAssignmentDeleteModal')->close();
        $this->deletingAssignmentId = null;

        $this->dispatch('classroom-data-changed', selectedClassroomId: $classroomId);

        Flux::toast(variant: 'success', text: __('Đã xoá môn học khỏi lớp.'));
    }

    public function saveYouthAssignments(): void
    {
        $classroomId = $this->selectedClassroomId;

        if ($classroomId === null) {
            Flux::toast(variant: 'warning', text: __('Vui lòng chọn lớp học trước.'));

            return;
        }

        $validated = $this->validate([
            'youth_ids' => ['array'],
            'youth_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $youthIds = collect($validated['youth_ids'] ?? [])
            ->map(fn (int|string $youthId): int => (int) $youthId)
            ->unique()
            ->values()
            ->all();

        $youthCount = User::query()
            ->whereKey($youthIds)
            ->whereHas('roles', fn ($query) => $query->where('name', 'thiếu nhi'))
            ->count();

        if ($youthCount !== count($youthIds)) {
            $this->addError('youth_ids', __('Vui lòng chỉ chọn tài khoản thiếu nhi.'));

            return;
        }

        $classroom = Classroom::query()->findOrFail($classroomId);
        $classroom->youths()->sync($youthIds);

        Flux::modal('showYouthModal')->close();

        $this->dispatch('classroom-data-changed', selectedClassroomId: $classroom->id);

        Flux::toast(variant: 'success', text: __('Đã cập nhật danh sách thiếu nhi của lớp.'));
    }

    public function closeFormModal(): void
    {
        Flux::modal('showFormModal')->close();
        $this->editingClassroomId = null;
        $this->resetForm();
    }

    public function closeDeleteModal(): void
    {
        Flux::modal('showDeleteModal')->close();
        $this->deletingClassroomId = null;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function closeAssignmentModal(): void
    {
        Flux::modal('showAssignmentModal')->close();
        $this->isAssignmentModalOpen = false;
        $this->editingAssignmentId = null;
        $this->resetAssignmentForm();
    }

    public function closeAssignmentDeleteModal(): void
    {
        Flux::modal('showAssignmentDeleteModal')->close();
        $this->deletingAssignmentId = null;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function closeYouthModal(): void
    {
        Flux::modal('showYouthModal')->close();
        $this->isYouthModalOpen = false;
        $this->youth_ids = [];
        $this->resetErrorBag();
        $this->resetValidation();
    }

    #[Computed]
    public function classroomPendingDeletion(): ?Classroom
    {
        if ($this->deletingClassroomId === null) {
            return null;
        }

        return Classroom::query()->find($this->deletingClassroomId);
    }

    #[Computed]
    public function assignmentPendingDeletion(): ?ClassroomSubject
    {
        if ($this->deletingAssignmentId === null) {
            return null;
        }

        return ClassroomSubject::query()
            ->with(['classroom', 'subject', 'teachers'])
            ->find($this->deletingAssignmentId);
    }

    #[Computed]
    public function availableStatuses(): array
    {
        return [
            'pending' => __('Chờ mở lớp'),
            'open' => __('Đang mở'),
            'closed' => __('Đã đóng'),
        ];
    }

    #[Computed]
    public function availableAssignmentStatuses(): array
    {
        return [
            'active' => __('Đang sử dụng'),
            'inactive' => __('Ngưng sử dụng'),
        ];
    }

    #[Computed]
    public function availableSubjects(): Collection
    {
        if (! $this->isAssignmentModalOpen) {
            return new Collection;
        }

        return Subject::query()
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function availableTeachers(): Collection
    {
        if (! $this->isAssignmentModalOpen) {
            return new Collection;
        }

        return User::query()
            ->whereHas('roles', fn ($query) => $query->where('name', 'giáo viên'))
            ->when(filled($this->assignment_name), function ($query) {
                $search = trim($this->assignment_name);

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('holy_name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function availableYouths(): Collection
    {
        if (! $this->isYouthModalOpen) {
            return new Collection;
        }

        return User::query()
            ->role('thiếu nhi')
            ->with('classrooms:id,name,code')
            ->when($this->youthSearch, function ($query): void {
                $search = trim($this->youthSearch);

                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('holy_name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get();
    }

    protected function createRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255', Rule::unique(Classroom::class, 'code')],
            'description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:pending,open,closed'],
        ];
    }

    protected function updateRules(int $classroomId): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255', Rule::unique(Classroom::class, 'code')->ignore($classroomId)],
            'description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:pending,open,closed'],
        ];
    }

    protected function createAssignmentRules(int $classroomId): array
    {
        return [
            'subject_id' => [
                'required',
                'exists:subjects,id',
                Rule::unique('classroom_subject', 'subject_id')->where(
                    fn ($query) => $query->where('classroom_id', $classroomId),
                ),
            ],
            'teacher_ids' => ['required', 'array', 'min:1'],
            'teacher_ids.*' => ['integer', 'exists:users,id'],
            'assignment_status' => ['required', 'in:active,inactive'],
        ];
    }

    protected function updateAssignmentRules(int $classroomId, int $assignmentId): array
    {
        return [
            'subject_id' => [
                'required',
                'exists:subjects,id',
                Rule::unique('classroom_subject', 'subject_id')
                    ->where(fn ($query) => $query->where('classroom_id', $classroomId))
                    ->ignore($assignmentId),
            ],
            'teacher_ids' => ['required', 'array', 'min:1'],
            'teacher_ids.*' => ['integer', 'exists:users,id'],
            'assignment_status' => ['required', 'in:active,inactive'],
        ];
    }

    protected function resetForm(): void
    {
        $this->name = '';
        $this->code = '';
        $this->description = '';
        $this->start_date = null;
        $this->end_date = null;
        $this->status = 'open';
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function updatedAssignmentName($value): void
    {
        $this->assignment_name = $value;
    }

    protected function resetAssignmentForm(): void
    {
        $this->subject_id = null;
        $this->teacher_ids = [];
        $this->assignment_status = 'active';
        $this->resetErrorBag();
        $this->resetValidation();
    }

    protected function resolveId(mixed $params): ?int
    {
        $id = is_array($params) ? ($params['id'] ?? null) : $params;

        if (! is_int($id) && ! ctype_digit((string) $id)) {
            return null;
        }

        return (int) $id;
    }

    public function render(): View
    {
        return view('livewire.admin.management.classroom.action');
    }
}
