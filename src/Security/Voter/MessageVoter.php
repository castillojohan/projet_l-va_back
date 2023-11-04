<?php

namespace App\Security\Voter;

use App\Entity\Message;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class MessageVoter extends Voter
{
    /**
     * Check if the voter supports the given attribute and subject.
     *
     * @param string $attribute The attribute to check.
     * @param Message $subject The subject to check.
     *
     * @return bool Returns true if the voter supports the attribute and subject; otherwise, false.
     */
    protected function supports(string $attribute, $subject): bool
    {
        // If the attribute isn't one we support, return false
        if (!in_array($attribute, ['view', 'other_action'])) {
            return false;
        }

        // Only vote on `Message` objects
        if (!$subject instanceof Message) {
            return false;
        }

        return true;
    }

    /**
     * Check whether the user has permission to perform the given action on the subject.
     *
     * @param string $attribute The attribute being checked.
     * @param Message $subject The subject being checked.
     * @param TokenInterface $token The authentication token.
     *
     * @return bool Returns true if the user has permission; otherwise, false.
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // If the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // Check conditions and return true to grant permission
        switch ($attribute) {
            case 'view':
                $message = $subject;
                if ($user === $message->getSender() || $user === $message->getRecipient()) {
                    return true;
                }
                break;
            case 'other_action':
                break;
        }

        return false;
    }
}
