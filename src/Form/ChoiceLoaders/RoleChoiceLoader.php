<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\ChoiceLoaders;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RoleChoiceLoader implements ChoiceLoaderInterface
{
    public const array ROLES = [
        'ROLE_USER',
        'ROLE_CHURCH_ADMIN',
        'ROLE_USER_ADMIN',
        'ROLE_ADMIN',
        'ROLE_SUPER_ADMIN',
        'ROLE_ALLOWED_TO_SWITCH',
    ];

    public function __construct(
        private readonly Security $security,
        private readonly TranslatorInterface $translator,
    ) {
    }

    private function getUser(): User
    {
        $user = $this->security->getUser();

        if ($user === null) {
            throw new \RuntimeException('Role choice loader determinate choices based on logged in user.');
        }

        if (!$user instanceof User) {
            throw new \RuntimeException(sprintf('Wrong user type. Expected: %s', User::class));
        }

        return $user;
    }

    /**
     * @param array<string> $roles
     *
     * @return array<string>
     */
    private function choiceForRoles(array $roles): array
    {
        $buffer = [];

        foreach ($roles as $role) {
            $buffer[$this->translator->trans('roles.'.$role)] = $role;
        }

        return $buffer;
    }

    #[\Override]
    public function loadChoiceList(?callable $value = null): ChoiceListInterface
    {
        $user = $this->getUser();
        $roles = $user->getRoles();

        // barmit csinalhat
        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return new ArrayChoiceList($this->choiceForRoles(self::ROLES));
        }

        // sima user semmit nem csinalhat (nem is szabad latnia a jogvalasztot
        if (['ROLE_USER'] === $roles) {
            return new ArrayChoiceList([]);
        }

        // alap, errol nem mondhat le
        if (($roleUserKey = array_search('ROLE_USER', $roles)) !== false) {
            unset($roles[$roleUserKey]);
        }

        return new ArrayChoiceList($this->choiceForRoles($roles));
    }

    #[\Override]
    public function loadChoicesForValues(array $values, ?callable $value = null): array
    {
        return $values;
    }

    #[\Override]
    public function loadValuesForChoices(array $choices, ?callable $value = null): array
    {
        return $choices;
    }
}
