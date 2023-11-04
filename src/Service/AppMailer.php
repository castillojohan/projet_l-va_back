<?php

namespace App\Service;

use App\Entity\CaseFolder;
use App\Entity\User;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Application Mailer
 */
class AppMailer
{
    /** @var Mailer $mailer */
    private $mailer;

    /** @var string $prefix */
    private $prefix;

    public function __construct(MailerInterface $mailer, string $prefix)
    {
        $this->mailer = $mailer;
        $this->prefix = $prefix;
    }

    /**
     * Sends an email when a new user create an account.
     *
     * @param User $user The user for whom the account is created.
     */
    public function createAccount(User $user)
    {
        // mail de confirmation
        $email = (new Email())
            ->from('assoleva23@gmail.com')
            ->to($user->getEmail())
            ->subject($this->prefix . 'Votre compte a été créé !')
            ->html('<h1>Compte créé</h1> <p>Votre compte a été créé:'  . $user->getPseudo() . '</p>');

        $this->mailer->send($email);
    }

    /**
     * Sends an email when a user's account moves to "certified".
     * 
     * @param User $user The user whose account is being certified.
     */
    public function certifiedUser(User $user)
    {
        // mail de confirmation
        $email = (new Email())
            ->from('assoleva23@gmail.com')
            ->to($user->getEmail())
            ->subject($this->prefix . 'Votre compte a été certifié !')
            ->html('<h1>Compte certifié !</h1> <p>Votre compte a été certifié par Leva' . $user->getPseudo() . '</p>');

        $this->mailer->send($email);
    }

    /**
     * Sends an email to notify a user that their case folder is in progress.
     *
     * @param CaseFolder $caseFolder The case folder in progress.
     * @param User $user The user associated with the case folder.
     */
    public function ongoingFolder(CaseFolder $caseFolder, User $user)
    {
        // mail de confirmation
        $email = (new Email())
            ->from('assoleva23@gmail.com')
            ->to($caseFolder->getUser()->getEmail())
            ->subject($this->prefix . 'Votre dossier est en cours de traitement')
            ->html('<h1>Dossier en cours de traitement !</h1> <p>Votre dossier a été approuvé par Léva et est actuellement en cours de traitement </p>');

        $this->mailer->send($email);
    }


     /**
     * Sends an email to notify a user that their case folder has been processed.
     *
     * @param CaseFolder $caseFolder The case folder that has been processed.
     * @param User $user The user associated with the case folder.
     */
    public function processedFolder(CaseFolder $caseFolder, User $user)
    {
        // mail de confirmation
        $email = (new Email())
            ->from('assoleva23@gmail.com')
            ->to($caseFolder->getUser()->getEmail())
            ->subject($this->prefix . 'Votre dossier a été traité')
            ->html('<h1>Dossier traité !</h1> <p>Votre dossier a été traité' . $user->getPseudo() . '</p>');         

        $this->mailer->send($email);
    }

     /**
     * Sends an email to notify a user that their account has been deactivated.
     *
     * @param CaseFolder $caseFolder The case folder associated with the user's account.
     * @param User $user The user whose account has been deactivated.
     */
    public function desactivatedUser(User $user)
    {
        // mail de confirmation
        $email = (new Email())
            ->from('assoleva23@gmail.com')
            ->to($user->getEmail())
            ->subject($this->prefix . 'compte a été désactivé')
            ->html('<h1>Compte désactivé!</h1><p></p>')
            ->text('Votre compe a été désactivé' . $user->getPseudo());

        $this->mailer->send($email);
    }


    public function recoveryLink(User $user)
    {
    // mail de confirmation
    $email = (new Email())
        ->from('assoleva23@gmail.com')
        ->to($user->getEmail())
        ->subject($this->prefix . 'Mot de passe oublié')
        ->html('<h1>Mot de passe Oublié</h1><p> <a href="localhost:5173/reset_password?key=' . $user->getForgotPasswordToken() . '">Réinitialiser mon mot de passe</a></p>')
        ->text('Vous avez demandé un nouveau mot de passe' . $user->getPseudo());

    $this->mailer->send($email);
    }


    public function passwordReset(User $user)
    {
    // mail de confirmation
    $email = (new Email())
        ->from('assoleva23@gmail.com')
        ->to($user->getEmail())
        ->subject($this->prefix . 'Mot de passe modifié')
        ->html('<h1>Mot de passe modifié</h1><p></p>')
        ->text('Votre mot de passe a bien été modifié.' . $user->getPseudo());

    $this->mailer->send($email);
    }


    public function reportDone (User $user)
    {
    // mail de confirmation
    $email = (new Email())
        ->from('assoleva23@gmail.com')
        ->to($user->getEmail())
        ->subject($this->prefix . 'Votre report a été validé')
        ->html('<h1>Report validé</h1> <p>Votre report a été validé par Léva, vous serez notifiez de l\'évolution du traitement </p>');

    $this->mailer->send($email);
    }
}