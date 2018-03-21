<?php

namespace AppBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use AppBundle\Entity\Game;
use AppBundle\Entity\DowntimePeriod;
use AppBundle\Entity\User;
use AppBundle\Entity\Member;

/**
 * Makes access decisions for Games and the things owned by games.
 */
class GameVoter extends Voter
{
    // If someone can do game organizer functions
    const ORGANIZE = 'CAN_ORGANIZE';

    protected function supports($attribute, $subject)
    {
        // Check that we support the attribute
        if (!in_array($attribute, array(self::ORGANIZE))) {
            return false;
        }

        // We only vote on Games
        if (! (($subject instanceof Game) || $subject instanceof DowntimePeriod)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        if ($subject instanceof DowntimePeriod) {
            $game = $subject->getGame();
        } else {
            $game = $subject;
        }

        dump($subject);

        switch ($attribute) {
            case self::ORGANIZE:
                return $this->canOrganize($game, $user);
        }
    }

    private function canOrganize(Game $game, User $user) {
        $user_memberships = $user->getMembers();

        dump($user_memberships);

        foreach ($user_memberships->getIterator() as $iterator => $member) {
            if ( ( $member->getGame() == $game ) && ($member->getPosition () == 'organizer') ) {
                return true;
            }
        }

        return false;
    }

}