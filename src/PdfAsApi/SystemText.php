<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\PdfAsApi;

use Dbp\Relay\EsignBundle\Configuration\Profile;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\PropertyEntry;

class SystemText
{
    /**
     * @param SystemDefinedText[] $systemText
     *
     * @return PropertyEntry[]
     */
    public static function buildSystemTextConfigOverride(Profile $profile, array $systemText, ?string $dateTrans = null): array
    {
        $systemTextConfig = $profile->getSystemText();
        if ($systemTextConfig === null) {
            throw new SigningException('system_text not available/implemented for this profile');
        }

        $profileId = $profile->getProfileId();

        // User text specific placement config
        $systemTable = $systemTextConfig->getTargetTable();
        $systemRow = $systemTextConfig->getTargetRow();

        $checkID = function ($name) {
            return preg_match('/[^.-]*/', $name) && $name !== '';
        };

        // validate the config, so we don't write out invalid config lines
        if (!$checkID($systemTable)) {
            throw new \RuntimeException('invalid table id');
        }
        if ($systemRow <= 0) {
            throw new \RuntimeException('invalid table row');
        }
        if (!$checkID($profileId)) {
            throw new \RuntimeException('invalid profile id');
        }

        // First we insert the user content into the table
        $overrides = [];
        foreach ($systemText as $entry) {
            $desc = $entry->getDescription();
            $value = $entry->getValue();

            $entryId = 'SIG_SYSTEM_TEXT_'.$systemTable.'_'.$systemRow;
            $overrides[] = new PropertyEntry("sig_obj.$profileId.key.$entryId", $desc);
            $overrides[] = new PropertyEntry("sig_obj.$profileId.value.$entryId", $value);
            $overrides[] = new PropertyEntry("sig_obj.$profileId.table.$systemTable.$systemRow", $entryId.'-cv');
            ++$systemRow;
        }

        // add date to table
        $overrides[] = new PropertyEntry("sig_obj.$profileId.key.SIG_DATE", $dateTrans);
        $overrides[] = new PropertyEntry("sig_obj.$profileId.table.$systemTable.$systemRow", 'SIG_DATE-cv');

        if ($systemTextConfig->hasAttach()) {
            $attachParent = $systemTextConfig->getAttachParentTable();
            $attachChild = $systemTextConfig->getAttachChildTable();
            $attachRow = $systemTextConfig->getAttachParentRow();

            if (!$checkID($attachChild)) {
                throw new \RuntimeException('invalid parent id');
            }
            if (!$checkID($attachParent)) {
                throw new \RuntimeException('invalid child id');
            }
            if ($attachRow <= 0) {
                throw new \RuntimeException('invalid table row');
            }

            // In case we added something we optionally attach a "child" table to a "parent" one at a specific "row"
            // This can be the table we filled above, or some parent table.
            // This is needed because pdf-as doesn't allow empty tables and we need to attach it only when it has at least
            // one row. But it also allows us to show extra images for example if there are >0 extra rows
            if (count($overrides) > 2) {
                $overrides[] = new PropertyEntry(
                    "sig_obj.$profileId.table.$attachParent.$attachRow", 'TABLE-'.$attachChild);
            }
        }

        return $overrides;
    }
}
