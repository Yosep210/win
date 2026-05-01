<?php

namespace App\Livewire\Membership;

use App\Models\Membership;
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

final class MembershipTable extends PowerGridComponent
{
    private const EDIT_EVENT = 'membership:edit';

    public string $tableName = 'membershipTable';

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
        return Membership::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('user_id')
            ->add('package_id')
            ->add('rank_id')
            ->add('as_stockist')
            ->add('is_stockist_central')
            ->add('stockist_name')
            ->add('stockist_province_id')
            ->add('stockist_city_id')
            ->add('stockist_district_id')
            ->add('stockist_village')
            ->add('stockist_address')
            ->add('wd_status')
            ->add('wd_min')
            ->add('is_ro_enabled')
            ->add('joined_at_formatted', fn (Membership $model) => Carbon::parse($model->joined_at)->format('d M Y H:i'))
            ->add('upgraded_at_formatted', fn (Membership $model) => Carbon::parse($model->upgraded_at)->format('d M Y H:i'))
            ->add('stockist_at_formatted', fn (Membership $model) => Carbon::parse($model->stockist_at)->format('d M Y H:i'))
            ->add('last_ro_at_formatted', fn (Membership $model) => Carbon::parse($model->last_ro_at)->format('d M Y H:i'))
            ->add('created_at_formatted', fn (Membership $model) => Carbon::parse($model->created_at)->format('d M Y H:i'));
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),
            Column::make('User id', 'user_id'),
            Column::make('Package id', 'package_id'),
            Column::make('Rank id', 'rank_id'),
            Column::make('As stockist', 'as_stockist')->sortable(),
            Column::make('Is stockist central', 'is_stockist_central')->sortable(),
            Column::make('Stockist name', 'stockist_name')->sortable(),
            Column::make('Stockist province id', 'stockist_province_id'),
            Column::make('Stockist city id', 'stockist_city_id'),
            Column::make('Stockist district id', 'stockist_district_id'),
            Column::make('Stockist village', 'stockist_village')->sortable(),
            Column::make('Stockist address', 'stockist_address')->sortable(),
            Column::make('Wd status', 'wd_status')->sortable(),
            Column::make('Wd min', 'wd_min')->sortable(),
            Column::make('Is ro enabled', 'is_ro_enabled')->sortable(),
            Column::make('Joined at', 'joined_at_formatted', 'joined_at')->sortable(),
            Column::make('Upgraded at', 'upgraded_at_formatted', 'upgraded_at')->sortable(),
            Column::make('Stockist at', 'stockist_at_formatted', 'stockist_at')->sortable(),
            Column::make('Last ro at', 'last_ro_at_formatted', 'last_ro_at')->sortable(),
            Column::make('Created at', 'created_at_formatted', 'created_at')->sortable(),
            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::datetimepicker('joined_at'),
            Filter::datetimepicker('upgraded_at'),
            Filter::datetimepicker('stockist_at'),
            Filter::datetimepicker('last_ro_at'),
            Filter::datetimepicker('created_at'),
        ];
    }

    #[On(self::EDIT_EVENT)]
    public function edit(int $rowId): void
    {
        Flux::toast(variant: 'warning', text: "Fitur edit Membership belum diimplementasikan. ID: {$rowId}");
    }

    public function actions(Membership $row): array
    {
        return [
            Button::add('edit')
                ->slot('Edit: '.$row->id)
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch(self::EDIT_EVENT, ['rowId' => $row->id]),
        ];
    }
}
