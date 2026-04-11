<?php

namespace App\Livewire\Admin\Management\Subject;

use App\Models\Subject;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class Action extends Component
{
    public ?int $editingSubjectId = null;

    public ?int $deletingSubjectId = null;

    public string $name = '';

    public string $code = '';

    public string $description = '';

    public string $status = 'active';

    public function mount(): void
    {
        $this->resetForm();
    }

    #[On('create-subject')]
    public function openCreateModal(): void
    {
        $this->editingSubjectId = null;
        $this->resetForm();

        Flux::modal('showFormModal')->show();
    }

    #[On('edit-subject')]
    public function openEditModal($subjectId): void
    {
        if ($subjectId === null) {
            return;
        }

        $subject = Subject::query()->findOrFail($subjectId);

        $this->editingSubjectId = $subject->id;
        $this->name = $subject->name;
        $this->code = $subject->code ?? '';
        $this->description = $subject->description ?? '';
        $this->status = $subject->status;
        $this->resetErrorBag();
        $this->resetValidation();

        Flux::modal('showFormModal')->show();
    }

    #[On('delete-subject')]
    public function openDeleteModal($subjectId): void
    {
        
        if ($subjectId === null) {
            return;
        }

        $this->deletingSubjectId = $subjectId;
        $this->resetErrorBag();
        $this->resetValidation();

        Flux::modal('showDeleteModal')->show();
    }

    public function updatedCode(string $value): void
    {
        $this->code = Str::upper($value);
    }

    public function saveSubject(): void
    {
        $isEditing = $this->editingSubjectId !== null;

        $this->code = Str::upper($this->code);

        $validated = $this->validate(
            $this->editingSubjectId === null
                ? $this->createRules()
                : $this->updateRules($this->editingSubjectId),
        );

        if (blank($validated['code'])) {
            $validated['code'] = null;
        }

        if ($isEditing) {
            $subject = Subject::query()->findOrFail($this->editingSubjectId);
            $subject->update($validated);
        } else {
            Subject::query()->create($validated);
        }

        Flux::modal('showFormModal')->close();
        $this->editingSubjectId = null;
        $this->resetForm();

        $this->dispatch('subject-updated');

        Flux::toast(
            variant: 'success',
            text: $isEditing ? __('Đã cập nhật môn học.') : __('Đã tạo môn học mới.'),
        );
    }

    public function deleteSubject(): void
    {
        $subject = Subject::query()->findOrFail($this->deletingSubjectId);
        $subject->delete();

        Flux::modal('showDeleteModal')->close();
        $this->deletingSubjectId = null;

        $this->dispatch('subject-updated');

        Flux::toast(variant: 'success', text: __('Đã xoá môn học.'));
    }

    public function closeFormModal(): void
    {
        Flux::modal('showFormModal')->close();
        $this->editingSubjectId = null;
        $this->resetForm();
    }

    public function closeDeleteModal(): void
    {
        Flux::modal('showDeleteModal')->close();
        $this->deletingSubjectId = null;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    #[Computed]
    public function subjectPendingDeletion(): ?Subject
    {
        if ($this->deletingSubjectId === null) {
            return null;
        }

        return Subject::query()->find($this->deletingSubjectId);
    }

    #[Computed]
    public function availableStatuses(): array
    {
        return [
            'active' => __('Đang sử dụng'),
            'inactive' => __('Ngưng sử dụng'),
        ];
    }

    protected function createRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255', Rule::unique(Subject::class, 'code')],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    protected function updateRules(int $subjectId): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255', Rule::unique(Subject::class, 'code')->ignore($subjectId)],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    protected function resetForm(): void
    {
        $this->name = '';
        $this->code = '';
        $this->description = '';
        $this->status = 'active';
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
        return view('livewire.admin.management.subject.action');
    }
}
