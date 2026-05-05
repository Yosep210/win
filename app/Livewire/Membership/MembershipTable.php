<?php

namespace App\Livewire\Membership;

use App\Livewire\DynamicModalForm;
use App\Models\Membership;
use App\Support\Forms\MembershipForm;
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
        $allowedSorts = ['user_name', 'user_username', 'user_email', 'package_name', 'package_code', 'rank_name', 'rank_code', 'as_stockist', 'is_stockist_central', 'stockist_name', 'stockist_province_id', 'stockist_city_id', 'stockist_district_id', 'stockist_village', 'wd_status', 'wd_min', 'is_ro_enabled', 'joined_at', 'upgraded_at', 'stockist_at', 'last_ro_at', 'created_at'];
        $sortField = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'memberships.id';

        // Map alias fields to actual table columns for ROW_NUMBER
        $rowNumberSortField = match ($sortField) {
            'user_name' => 'users.name',
            'user_username' => 'users.username',
            'user_email' => 'users.email',
            'package_name' => 'packages.name',
            'package_code' => 'packages.code',
            'rank_name' => 'ranks.name',
            'rank_code' => 'ranks.code',
            'as_stockist' => 'memberships.as_stockist',
            'is_stockist_central' => 'memberships.is_stockist_central',
            'stockist_name' => 'memberships.stockist_name',
            'stockist_province_id' => 'memberships.stockist_province_id',
            'stockist_city_id' => 'memberships.stockist_city_id',
            'stockist_district_id' => 'memberships.stockist_district_id',
            'stockist_village' => 'memberships.stockist_village',
            'wd_status' => 'memberships.wd_status',
            'wd_min' => 'memberships.wd_min',
            'is_ro_enabled' => 'memberships.is_ro_enabled',
            'joined_at' => 'memberships.joined_at',
            'upgraded_at' => 'memberships.upgraded_at',
            'stockist_at' => 'memberships.stockist_at',
            'last_ro_at' => 'memberships.last_ro_at',
            'created_at' => 'memberships.created_at',
            default => 'memberships.id'
        };

        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        return Membership::query()
            ->leftJoin('users', 'memberships.user_id', '=', 'users.id')
            ->leftJoin('packages', 'memberships.package_id', '=', 'packages.id')
            ->leftJoin('ranks', 'memberships.rank_id', '=', 'ranks.id')
            ->select(
                'memberships.*',
                'users.name as user_name',
                'users.username as user_username',
                'users.email as user_email',
                'packages.name as package_name',
                'packages.code as package_code',
                'ranks.name as rank_name',
                'ranks.code as rank_code'
            )
            ->selectRaw('ROW_NUMBER() OVER (ORDER BY '.$rowNumberSortField.' '.$sortDirection.') AS no');
    }

    public function relationSearch(): array
    {
        return [
            'user' => [
                'name',
                'username',
                'email',
            ],
            'package' => [
                'name',
                'code',
            ],
            'rank' => [
                'name',
                'code',
            ],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('no')
            ->add('user_name')
            ->add('user_username')
            ->add('user_email')
            ->add('package_name')
            ->add('package_code')
            ->add('rank_name')
            ->add('rank_code')
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
            Column::make('#', 'no'),
            Column::make('User Name', 'user_name')->sortable(),
            Column::make('Username', 'user_username')->sortable(),
            Column::make('Email', 'user_email')->sortable(),
            Column::make('Package', 'package_name')->sortable(),
            Column::make('Package Code', 'package_code')->sortable(),
            Column::make('Rank', 'rank_name')->sortable(),
            Column::make('Rank Code', 'rank_code')->sortable(),
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
            Filter::inputText('user_name')->operators(['contains']),
            Filter::inputText('user_username')->operators(['contains']),
            Filter::inputText('user_email')->operators(['contains']),
            Filter::inputText('package_name')->operators(['contains']),
            Filter::inputText('package_code')->operators(['contains']),
            Filter::inputText('rank_name')->operators(['contains']),
            Filter::inputText('rank_code')->operators(['contains']),
            Filter::inputText('stockist_name')->operators(['contains']),
            Filter::inputText('wd_status')->operators(['contains']),
            Filter::datetimepicker('joined_at'),
            Filter::datetimepicker('upgraded_at'),
            Filter::datetimepicker('stockist_at'),
            Filter::datetimepicker('last_ro_at'),
            Filter::datetimepicker('created_at'),
        ];
    }

    #[On(MembershipForm::EDIT_EVENT)]
    public function edit(int $rowId): void
    {
        $config = MembershipForm::make('Edit Membership', modelId: $rowId);
        $this->dispatch('open-dynamic-modal', config: $config)->to(DynamicModalForm::class);
    }

    #[On(MembershipForm::DELETE_EVENT)]
    public function delete(int $rowId): void
    {
        Membership::findOrFail($rowId)->delete();
        $this->dispatch(MembershipForm::REFRESH_EVENT)->to(self::class);
        Flux::toast(variant: 'success', text: 'Data membership berhasil dihapus.');
    }

    public function actions(Membership $row): array
    {
        return [
            Button::add('edit')
                ->slot('Edit')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch(MembershipForm::EDIT_EVENT, ['rowId' => $row->id]),
            Button::add('delete')
                ->slot('Delete')
                ->id()
                ->class('pg-btn-white dark:ring-pg-red-600 dark:border-pg-red-600 dark:hover:bg-pg-red-700 dark:ring-offset-pg-red-800 dark:text-pg-red-300 dark:bg-pg-red-700')
                ->confirm('Apakah Anda yakin ingin menghapus membership ini?')
                ->dispatch(MembershipForm::DELETE_EVENT, ['rowId' => $row->id]),
        ];
    }
}
