<?php

namespace App\Admin\Controllers;

use App\Models\Client;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ClientController extends AdminController
{
    protected $title = 'Клиенты';

    protected function grid()
    {
        $grid = new Grid(new Client());

        $grid->column('id', __('ID'))->sortable();
        $grid->column('name', __('Имя'));
        $grid->column('phone', __('Телефон'));
        $grid->column('created_at', __('Создано'))->sortable();
        $grid->column('updated_at', __('Обновлено'))->sortable();

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(Client::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('name', __('Имя'));
        $show->field('phone', __('Телефон'));
        $show->field('created_at', __('Создано'));
        $show->field('updated_at', __('Обновлено'));

        return $show;
    }

    protected function form()
    {
        $form = new Form(new Client());

        $form->text('name', __('Имя'))->required();
        $form->mobile('phone', __('Телефон'))->required();

        return $form;
    }
}
