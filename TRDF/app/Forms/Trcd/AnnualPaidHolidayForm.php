<?php

namespace App\Forms\Trcd;

use App\Forms\BaseForm;

class AnnualPaidHolidayForm extends BaseForm
{
    public function buildForm()
    {
        $this->add('base_date', 'text', [
            'label' => '基準日（付与された日）',
            'rules' => ['required', 'date'],
            'attr' => [
                'readonly' => 'true',
            ],
        ]);

        $this->add('next_base_date', 'text', [
            'label' => '次の（予定）基準日',
            'rules' => ['required', 'date'],
            'attr' => [
                'readonly' => 'true',
            ],
        ]);

        $this->add('days_worked', 'text', [
            'label' => '勤務日数',
            'rules' => ['required', 'integer'],
            'attr' => [
                'readonly' => 'true',
            ],
        ]);

        $this->add('days_granted', 'text', [
            'label' => '付与日数',
            'rules' => ['required', 'integer'],
            'attr' => [
                'readonly' => 'true',
            ],
        ]);

        $this->add('days_used', 'text', [
            'label' => '取得（消化）日数',
            'rules' => ['required', 'regex:/^-?\d{1,3}(\.\d{0,2})?$/'],
            'rules' => ['required', 'regex:/^-?\d{1,3}(\.\d{0,2})?$/'],
            'attr' => [
                'readonly' => 'true',
            ],
        ]);

        $this->add('expiration_date', 'text', [
            'label' => '有効期限（時効）',
            'rules' => ['required', 'date'],
            'attr' => [
                'readonly' => 'true',
            ],
        ]);

        $this->addDaysAdded();

        $this->add('usable_days', 'text', [
            'label' => '取得（消化）可能な日数',
            'rules' => ['required', 'regex:/^-?\d{1,3}(\.\d{0,2})?$/'],
            'attr' => [
                'readonly' => 'true',
            ],
        ]);

        $this->add('submit', 'submit', [
            'label' => '更新',
            'attr' => [
                'class' => 'btn btn-primary',
            ],
        ]);
    }

    protected function addDaysAdded()
    {
        $lower_value = $this->isNew() ? 0 : $this->model['days_granted'];

        $this->add('days_added', 'text', [
            'label' => '追加付与日数（更新可能）',
            'rules' => [
                'required',
                'numeric',
                'regex:/^-?\d{1,3}(\.\d{0,2})?$/',
                "gte:" . gmp_neg($lower_value),
            ],
        ]);
    }
}
