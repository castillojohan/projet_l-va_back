<?php

namespace App\EventListener;

use App\Entity\CaseFolder;

class CaseFolderListener
{

    /**
     * Generates a reference for a case folder.
     *
     * The reference is created by combining the platform name, user's pseudonym,
     * the report's identifier, the case folder's identifier, followed by a random
     * number between 1 and 99.
     *
     * Example generated reference: "twitter-SynK-24665-12345-42"
     *
     * @param CaseFolder $caseFolder The case folder for which to generate the reference.
     * @return void
     */
    public function generateReference(CaseFolder $caseFolder)
    {
        // then: twitter-micheline-24665 (might be appear like that)
        //          -       -      -- -
        $reference = "{$caseFolder->getPlatform()->getName()}-{$caseFolder->getUser()->getPseudo()}-".uniqid();
        
        $caseFolder->setReference(strtolower($reference));
    }
}