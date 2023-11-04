<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class UserVoter extends Voter
{
    /**
     * Check if the voter supports the given attribute and subject.
     *
     * @param string $attribute The attribute to check.
     * @param User $subject The subject to check.
     *
     * @return bool Returns true if the voter supports the attribute and subject; otherwise, false.
     */
    protected function supports(string $attribute, $subject): bool
    {
        // If the attribute isn't one we support, return false
        if (!in_array($attribute, ['view', 'edit', 'addAndModify', 'other_action'])) {
            return false;
        }

        // Only vote on `User` objects
        if (!$subject instanceof User) {
            return false;
        }

        return true;
    }

    /**
     * Check whether the user has permission to perform the given action on the subject.
     *
     * @param string $attribute The attribute being checked.
     * @param User $subject The subject being checked.
     * @param TokenInterface $token The authentication token.
     *
     * @return bool Returns true if the user has permission; otherwise, false.
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        $userMail = $user->getUserIdentifier(); 
        $role = $token->getRoleNames();
        
        // If the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }
        
        // Check conditions and return true to grant permission
        switch ($attribute) {
            case 'view':
                if ($userMail === $subject->getUserIdentifier()) {
                    return true;
                }
                break;
            case 'edit':
                if ($userMail === $subject->getUserIdentifier()) {
                    return true;
                }
                break;
            case 'addAndModify':
                if ($role === ['ROLE_ADMIN']) {
                    return true;
                }
                break;
            case 'other_action':
                break;
        }

        return false;
    }
}
