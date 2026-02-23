<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\PdfAsApi;

use Dbp\Relay\EsignBundle\Configuration\Profile;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\PropertyEntry;

class UserText
{
    /**
     * @param string $imageData A PNG image
     */
    public static function buildUserImageConfigOverride(Profile $profile, string $imageData): PropertyEntry
    {
        // Don't allow arbitrarily large images, 500kb should be enough for any normal usage.
        if (strlen($imageData) > 1024 * 500) {
            throw new SigningException('signature image data too large');
        }
        // We only allow PNG since pdf-as will internally convert it, so allowing JPG would just lead to larger PDFs
        $finfo = new \finfo(\FILEINFO_MIME_TYPE);
        if ($finfo->buffer($imageData) !== 'image/png') {
            throw new SigningException('only image/png allowed for signature image');
        }

        return new PropertyEntry('sig_obj.'.$profile->getProfileId().'.value.SIG_LABEL', base64_encode($imageData));
    }

    /**
     * @param UserDefinedText[] $userText
     *
     * @return PropertyEntry[]
     */
    public static function buildUserTextConfigOverride(Profile $profile, array $userText): array
    {
        $userTextConfig = $profile->getUserText();
        if ($userTextConfig === null) {
            throw new SigningException('user_text not available/implemented for this profile');
        }

        $profileId = $profile->getProfileId();

        // User text specific placement config
        $userTable = $userTextConfig->getTargetTable();
        $userRow = $userTextConfig->getTargetRow();

        $checkID = function ($name) {
            return preg_match('/[^.-]*/', $name) && $name !== '';
        };

        // validate the config, so we don't write out invalid config lines
        if (!$checkID($userTable)) {
            throw new \RuntimeException('invalid table id');
        }
        if ($userRow <= 0) {
            throw new \RuntimeException('invalid table row');
        }
        if (!$checkID($profileId)) {
            throw new \RuntimeException('invalid profile id');
        }

        // First we insert the user content into the table
        $overrides = [];
        foreach ($userText as $entry) {
            $desc = $entry->getDescription();
            $value = $entry->getValue();

            $entryId = 'SIG_USER_TEXT_'.$userTable.'_'.$userRow;
            $overrides[] = new PropertyEntry("sig_obj.$profileId.key.$entryId", $desc);
            $overrides[] = new PropertyEntry("sig_obj.$profileId.value.$entryId", $value);
            $overrides[] = new PropertyEntry("sig_obj.$profileId.table.$userTable.$userRow", $entryId.'-cv');
            ++$userRow;
        }

        if ($userTextConfig->hasAttach()) {
            $attachParent = $userTextConfig->getAttachParentTable();
            $attachChild = $userTextConfig->getAttachChildTable();
            $attachRow = $userTextConfig->getAttachParentRow();

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
            if (count($overrides) > 0) {
                $overrides[] = new PropertyEntry(
                    "sig_obj.$profileId.table.$attachParent.$attachRow", 'TABLE-'.$attachChild);
            }
        }

        return $overrides;
    }
}
