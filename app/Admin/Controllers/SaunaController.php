<?php

namespace App\Admin\Controllers;

use App\Models\Sauna;
use App\Models\Company;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SaunaController extends AdminController
{
    protected $title = 'Сауны';

    protected function grid()
    {
        $grid = new Grid(new Sauna());

        $grid->column('id', __('ID'))->sortable();
        $grid->column('company.name', __('Компания'));
        $grid->column('name', __('Название'));
        $grid->column('description', __('Описание'));
        $grid->column('address', __('Адрес'));
        $grid->column('created_at', __('Создано'))->sortable();
        $grid->column('updated_at', __('Обновлено'))->sortable();

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(Sauna::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('company.name', __('Компания'));
        $show->field('name', __('Название'));
        $show->field('description', __('Описание'));
        $show->field('address', __('Адрес'));
        $show->field('created_at', __('Создано'));
        $show->field('updated_at', __('Обновлено'));

        return $show;
    }

    protected function form()
    {
        $form = new Form(new Sauna());

        $form->select('company_id', __('Компания'))->options(Company::all()->pluck('name', 'id'))->required();
        $form->text('name', __('Название'))->required();
        $form->textarea('description', __('Описание'));
        $form->text('address', __('Адрес'))->required();

        return $form;
    }
}
