<?php

namespace App\Livewire\Package;

use App\Models\Package;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class PackageTable extends PowerGridComponent
{
    private const EDIT_EVENT = 'package:edit';

    public string $tableName = 'packageTable';

    public function setUp(): array
    {
        return [
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Package::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('code')
            ->add('name')
            ->add('sort_order')
            ->add('package_count')
            ->add('bv')
            ->add('price')
            ->add('sponsor_percent')
            ->add('passup_percent')
            ->add('pairing_percent')
            ->add('pairing_nominal')
            ->add('pairing_max')
            ->add('pairing_point')
            ->add('reward_point')
            ->add('description')
            ->add('is_register')
            ->add('is_order')
            ->add('is_active')
            ->add('created_at_formatted', fn (Package $model) => Carbon::parse($model->created_at)->format('d M Y H:i'));
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),
            Column::make('Code', 'code')->sortable(),
            Column::make('Name', 'name')->sortable(),
            Column::make('Sort order', 'sort_order'),
            Column::make('Package count', 'package_count'),
            Column::make('Bv', 'bv'),
            Column::make('Price', 'price')->sortable(),
            Column::make('Sponsor percent', 'sponsor_percent')->sortable(),
            Column::make('Passup percent', 'passup_percent')->sortable(),
            Column::make('Pairing percent', 'pairing_percent')->sortable(),
            Column::make('Pairing nominal', 'pairing_nominal')->sortable(),
            Column::make('Pairing max', 'pairing_max'),
            Column::make('Pairing point', 'pairing_point'),
            Column::make('Reward point', 'reward_point')->sortable(),
            Column::make('Description', 'description')->sortable(),
            Column::make('Is register', 'is_register')->sortable(),
            Column::make('Is order', 'is_order')->sortable(),
            Column::make('Is active', 'is_active')->sortable(),
            Column::make('Created at', 'created_at_formatted', 'created_at')->sortable(),
            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::datetimepicker('created_at'),
        ];
    }

    #[On(self::EDIT_EVENT)]
    public function edit(int $rowId): void
    {
        Flux::toast(variant: 'warning', text: "Fitur edit Package belum diimplementasikan. ID: {$rowId}");
    }

    public function actions(Package $row): array
    {
        return [
            Button::add('edit')
                ->slot('Edit: '.$row->id)
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch(self::EDIT_EVENT, ['rowId' => $row->id]),
        ];
    }

    /*
    public function actionRules($row): array
    {
       return [
            // Hide button edit for ID 1
            Rule::button('edit')
                ->when(fn($row) => $row->id === 1)
                ->hide(),
        ];
    }
    */
}
