<?php

namespace App\Admin\Controllers;

use App\Models\Company;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CompanyController extends AdminController
{
    protected $title = 'Компании';

    protected function grid()
    {
        $grid = new Grid(new Company());

        $grid->column('id', __('ID'))->sortable();
        $grid->column('name', __('Название'));
        $grid->column('description', __('Описание'));
        $grid->column('data', __('Данные'));
        $grid->column('address', __('Адрес'));
        $grid->column('created_at', __('Создано'))->sortable();
        $grid->column('updated_at', __('Обновлено'))->sortable();

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(Company::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('name', __('Название'));
        $show->field('description', __('Описание'));
        $show->field('data', __('Данные'));
        $show->field('address', __('Адрес'));
        $show->field('created_at', __('Создано'));
        $show->field('updated_at', __('Обновлено'));

        return $show;
    }

    protected function form()
    {
        $form = new Form(new Company());

        $form->text('name', __('Название'))->required();
        $form->textarea('description', __('Описание'));
        $form->text('data', __('Данные'))->required();
        $form->text('address', __('Адрес'))->required();

        return $form;
    }
}
