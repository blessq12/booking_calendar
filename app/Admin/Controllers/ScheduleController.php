<?php

namespace App\Admin\Controllers;

use App\Models\Schedule;
use App\Models\Sauna;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ScheduleController extends AdminController
{
    protected $title = 'Расписание';

    protected function grid()
    {
        $grid = new Grid(new Schedule());

        $grid->column('id', __('ID'))->sortable();
        $grid->column('sauna.name', __('Сауна'));
        $grid->column('date', __('Дата'))->sortable();
        $grid->column('slots', __('Слоты'))->display(function ($slots) {
            return json_encode($slots);
        });
        $grid->column('created_at', __('Создано'))->sortable();
        $grid->column('updated_at', __('Обновлено'))->sortable();

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(Schedule::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('sauna.name', __('Сауна'));
        $show->field('date', __('Дата'));
        $show->field('slots', __('Слоты'))->json();
        $show->field('created_at', __('Создано'));
        $show->field('updated_at', __('Обновлено'));

        return $show;
    }

    protected function form()
    {
        $form = new Form(new Schedule());

        $form->select('sauna_id', __('Сауна'))->options(Sauna::all()->pluck('name', 'id'))->required();
        $form->date('date', __('Дата'))->required();
        $form->textarea('slots', __('Слоты'))->required();

        $form->saving(function (Form $form) {
            if (is_string($form->slots)) {
                $form->slots = json_decode($form->slots, true);
            }
        });

        return $form;
    }
}
