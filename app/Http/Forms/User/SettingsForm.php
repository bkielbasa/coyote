<?php

namespace Coyote\Http\Forms\User;

use Coyote\Services\FormBuilder\Form;
use Coyote\Group;
use Coyote\User;

class SettingsForm extends Form
{
    public function buildForm()
    {
        /** @var \Coyote\User $user */
        $user = $this->getData();

        // id uzytkownika, ktorego ustawienia wlasnie edytujemy
        $userId = $user->id;
        $groupList = $user->groups()->lists('name', 'id')->toArray();

        $this->add('email', 'email', [
            'rules' => 'required|email|unique:users,email,' . $userId . ',id,is_confirm,1',
            'label' => 'E-mail',
            'help' => 'Jeżeli chcesz zmienić adres e-mail, na nową skrzynkę zostanie wygenerowany klucz aktywacyjny.',
            'row_attr' => [
                'id' => 'email'
            ]
        ]);

        if ($groupList) {
            $this->add('group_id', 'select', [
                'rules' => 'sometimes|integer|exists:group_users,group_id,user_id,' . $userId,
                'label' => 'Domyślna grupa',
                'help' => 'Nazwa grupy będzie wyświetlana pod avatarem, np. na forum.',
                'choices' => $groupList,
                'empty_value' => '-- wybierz --'
            ]);
        }

        $this
            ->add('date_format', 'select', [
                'label' => 'Format daty',
                'choices' => User::dateFormatList()
            ])
            ->add('website', 'text', [
                'rules'  => 'url|reputation:50',
                'label' => 'Strona WWW',
                'help' => 'Strona domowa, blog, portfolio itp.'
            ])
            ->add('allow_smilies', 'checkbox', [
                'rules' => 'boolean',
                'label' => 'Pokazuj emotikony'
            ])
            ->add('allow_subscribe', 'checkbox', [
                'rules' => 'boolean',
                'label' => 'Automatycznie obserwuj wątki oraz wpisy na mikroblogu, w których biorę udział'
            ])
            ->add('firm', 'text', [
                'rules' => 'string|max:100',
                'label' => 'Nazwa firmy',
            ])
            ->add('position', 'text', [
                'rules' => 'string|max:100',
                'label' => 'Stanowisko',
                'attr' => [
                    'placeholder' => 'Np. Junior Java Developer'
                ]
            ])
            ->add('bio', 'textarea', [
                'rules' => 'string|max:500',
                'label' => 'O sobie',
                'help' => 'W tym polu możesz zamieścić krótką informację o sobie, czym się zajmujesz, co cię interesuje. Ta informacja zostanie wyświetlona na Twoim profilu',
                'attr' => [
                    'rows' => 3
                ]
            ])
            ->add('birthyear', 'select', [
                'rules' => 'sometimes|integer|between:1950,' . (date('Y') - 1),
                'label' => 'Rok urodzenia',
                'help' => 'Na podstawie roku urodzenia, w Twoim profilu będzie widoczny Twój wiek',
                'choices' => User::birthYearList(),
                'empty_value' => '--'
            ])
            ->add('location', 'text', [
                'rules' => 'string|max:50',
                'label' => 'Miejsce zamieszkania'
            ])
            ->add('allow_count', 'checkbox', [
                'rules' => 'boolean',
                'label' => 'Pokazuj licznik postów'
            ])
            ->add('allow_sig', 'checkbox', [
                'rules' => 'boolean',
                'label' => 'Pokazuj sygnaturki użytkowników'
            ])
            ->add('sig', 'textarea', [
                'rules' => 'string|max:255',
                'label' => 'Sygnatura',
                'help' => 'Podpis będzie widoczny przy każdym Twoim poście. Uwaga! Użytkownicy posiadający mniej niż 50 punktów reputacji nie mogą umieszczać linków w tym polu.',
                'attr' => [
                    'rows' => 3
                ]
            ])
            ->add('submit', 'submit', [
                'label' => 'Zapisz',
                'attr' => [
                    'data-submit-state' => 'Wysyłanie...'
                ]
            ]);
    }
}
