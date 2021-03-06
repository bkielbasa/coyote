<?php

namespace Coyote\Http\Forms;

use Carbon\Carbon;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\FormEvents;
use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;

class FirewallForm extends Form implements ValidatesWhenSubmitted
{
    /**
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        parent::__construct();

        $this->addEventListener(FormEvents::POST_SUBMIT, function (Form $form) use ($userRepository) {
            $username = $form->get('name')->getValue();
            $form->add('user_id', 'hidden');

            if ($username) {
                /** @var \Coyote\User $user */
                $user = $userRepository->findByName($username);

                $form->get('user_id')->setValue($user->id);
            }
        });

        $this->addEventListener(FormEvents::PRE_RENDER, function (Form $form) use ($userRepository) {
            if (!empty($this->data->user_id)) {
                $form->get('name')->setValue($userRepository->find($this->data->user_id, ['name'])->value('name'));
            }
        });
    }

    public function buildForm()
    {
        $this
            ->add('name', 'text', [
                'rules' => 'sometimes|string|username|user_exist',
                'label' => 'Nazwa użytkownika',
                'attr' => [
                    'id' => 'username',
                    'autocomplete' => 'off'
                ]
            ])
            ->add('ip', 'text', [
                'label' => 'IP',
                'rules' => 'sometimes|string|min:2|max:100',
                'help' => 'IP może zawierać znak *'
            ])
            ->add('reason', 'textarea', [
                'label' => 'Powód',
                'rules' => 'max:1000'
            ])
            ->add('expire_at', 'text', [
                'label' => 'Data wygaśnięcia',
                'rules' => 'sometimes|date_format:Y-m-d',
                'attr' => [
                    'id' => 'expire-at'
                ]
            ])
            ->add('lifetime', 'checkbox', [
                'label' => 'Bezterminowo',
                'checked' => empty($this->data->expire_at)
            ])
            ->add('submit', 'submit', [
                'label' => 'Zapisz',
                'attr' => [
                    'data-submit-state' => 'Zapisywanie...'
                ]
            ]);

        if (empty($this->data->id)) {
            $this->get('expire_at')->setValue(Carbon::now()->addDay(1));
        }
    }
}
