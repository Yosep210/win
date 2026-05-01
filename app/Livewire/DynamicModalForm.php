<?php

namespace App\Livewire;

use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Attributes\On;

class DynamicModalForm extends Component
{
    public string $title = 'Form';
    public string $modalName = 'dynamic-modal';
    public string $submitLabel = 'Simpan';
    public string $cancelLabel = 'Batal';
    public string $successMessage = 'Data berhasil disimpan.';
    public array $fields = [];
    public array $data = [];
    public ?string $modelClass = null;
    public ?int $modelId = null;
    public ?string $refreshEvent = null;
    public array $validationMessages = [];

    #[On('open-dynamic-modal')]
    public function openModal(array $config): void
    {
        $this->resetFormState();

        $this->title = $config['title'] ?? 'Form';
        $this->modalName = $config['modalName'] ?? 'dynamic-modal';
        $this->submitLabel = $config['submitLabel'] ?? 'Simpan';
        $this->cancelLabel = $config['cancelLabel'] ?? 'Batal';
        $this->successMessage = $config['successMessage'] ?? 'Data berhasil disimpan.';
        $this->fields = $config['fields'] ?? [];
        $this->modelClass = $config['modelClass'] ?? $config['model'] ?? null;
        $this->modelId = $config['modelId'] ?? $config['id'] ?? null;
        $this->refreshEvent = $config['refreshEvent'] ?? null;
        $this->validationMessages = $config['validationMessages'] ?? [];

        $existingModel = $this->resolveExistingModel();

        foreach ($this->fields as $field) {
            $name = $field['name'] ?? null;

            if (! $name) {
                continue;
            }

            $default = $field['default'] ?? $this->defaultValueForType($field['type'] ?? 'text');
            $this->data[$name] = $existingModel ? data_get($existingModel, $name) : $default;
        }

        $this->js("Flux.modal('{$this->modalName}').show()");
    }

    public function save(): void
    {
        $this->validate($this->rules(), $this->messages());

        if (! $this->isValidModelClass()) {
            Flux::toast(variant: 'danger', text: 'Model form tidak valid.');

            return;
        }

        /** @var Model $model */
        $model = $this->modelId
            ? $this->modelClass::query()->findOrFail($this->modelId)
            : new $this->modelClass();

        try {
            $model->fill($this->payload());
            $model->save();
        } catch (QueryException $exception) {
            if ($this->isUniqueConstraintViolation($exception)) {
                foreach ($this->uniqueFieldNames() as $field) {
                    $this->addError("data.$field", $this->validationMessages["data.$field.unique"] ?? 'Nilai sudah digunakan.');
                }

                return;
            }

            throw $exception;
        }

        if ($this->refreshEvent) {
            $this->dispatch($this->refreshEvent);
        }

        $this->js("Flux.modal('{$this->modalName}').close()");
        $this->dispatch('dynamic-modal-form.saved', model: $this->modelClass, id: $model->getKey());
        $this->resetFormState();

        Flux::toast(
            variant: 'success',
            text: $this->successMessage,
        );
    }

    public function render()
    {
        return view('livewire.shared.dynamic-modal-form');
    }

    protected function rules(): array
    {
        $rules = [];

        foreach ($this->fields as $field) {
            $name = $field['name'] ?? null;
            $validation = $field['validation'] ?? $field['rules'] ?? null;

            if (! $name || ! $validation) {
                continue;
            }

            $rules["data.{$name}"] = $validation;
        }

        return $rules;
    }

    protected function payload(): array
    {
        $allowedFields = collect($this->fields)
            ->pluck('name')
            ->filter()
            ->values()
            ->all();

        return Arr::only($this->data, $allowedFields);
    }

    protected function resetFormState(): void
    {
        $this->resetValidation();
        $this->reset([
            'data',
            'fields',
            'modelClass',
            'modelId',
            'refreshEvent',
            'validationMessages',
        ]);
        $this->title = 'Form';
        $this->modalName = 'dynamic-modal';
        $this->submitLabel = 'Simpan';
        $this->cancelLabel = 'Batal';
        $this->successMessage = 'Data berhasil disimpan.';
    }

    protected function resolveExistingModel(): ?Model
    {
        if (! $this->modelId || ! $this->isValidModelClass()) {
            return null;
        }

        return $this->modelClass::query()->findOrFail($this->modelId);
    }

    protected function isValidModelClass(): bool
    {
        return $this->modelClass
            && class_exists($this->modelClass)
            && is_subclass_of($this->modelClass, Model::class);
    }

    protected function defaultValueForType(string $type): mixed
    {
        return match (Str::lower($type)) {
            'checkbox' => false,
            default => '',
        };
    }

    protected function messages(): array
    {
        return $this->validationMessages;
    }

    protected function uniqueFieldNames(): array
    {
        return collect($this->fields)
            ->filter(function (array $field): bool {
                $validation = $field['validation'] ?? $field['rules'] ?? [];
                $rules = is_array($validation) ? $validation : [$validation];

                return collect($rules)->contains(fn (mixed $rule) => is_string($rule) && str_starts_with($rule, 'unique:'));
            })
            ->pluck('name')
            ->filter()
            ->values()
            ->all();
    }

    protected function isUniqueConstraintViolation(QueryException $exception): bool
    {
        $sqlState = $exception->errorInfo[0] ?? null;
        $driverCode = $exception->errorInfo[1] ?? null;

        return $sqlState === '23000' || $driverCode === 19;
    }
}
