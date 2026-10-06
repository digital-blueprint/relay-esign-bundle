<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\PdfAsApi;

use Dbp\Relay\EsignBundle\Configuration\Profile;

class SignatureReason
{
    /**
     * This tries to create a text representation of the signature block, which is then saved in `adobeSignReasonValue`,
     * which is visible in Adobe Reader and Edge for example. The motivation that the info isn't lost if the signature
     * block is invisible, but it's applied in all cases for consistency.
     *
     * Challenges
     *
     * - Adobe shows newlines in some places and collapses to one line in others, ideally it looks fine in both.
     * - Edge shows a single line only
     * - Adobe removes LF from the single line variant, but collapses CRLF to a space, so we normalize to CRLF.
     * - We add ";" separators at the end of lines to separate it from the next line in case it is collapsed.
     * - Adobe doesn't normalize spaces except CRLF, so intention of multiline values looks weird if collapsed
     * - Edge collapses multiple spaces everywhere.
     */
    public static function build(Profile $profile, SigningRequest $request, ?string $additionalInfoLabel = null): ?string
    {
        $systemLines = [];
        foreach ($request->getSystemText() as $entry) {
            $systemLines[] = self::formatEntry($entry->getDescription(), $entry->getValue());
        }

        $userLines = [];
        foreach ($request->getUserText() as $entry) {
            if ($entry->getType() !== UserDefinedText::$TYPE_IMAGE) {
                $userLines[] = self::formatEntry($entry->getDescription(), $entry->getValue());
            }
        }

        $sections = [];
        if ($systemLines !== []) {
            $sections[] = implode(";\n", $systemLines);
        }
        if ($userLines !== []) {
            $userSection = implode(";\n", $userLines);
            $userTextConfig = $profile->getUserText();
            if ($additionalInfoLabel !== null && $userTextConfig !== null && $userTextConfig->hasAttach() && $userTextConfig->getSeparator()) {
                $userSection = $additionalInfoLabel.":\n".$userSection;
            }
            $sections[] = $userSection;
        }

        if ($sections === []) {
            return null;
        }

        // Keep visible separators so the reason remains readable when a viewer flattens line breaks.
        $reason = implode(";\n", $sections);

        // Normalize user-provided and generated line breaks to CRLF for the PDF reason field.
        return str_replace("\n", "\r\n", str_replace(["\r\n", "\r"], "\n", $reason));
    }

    private static function formatEntry(string $description, string $value): string
    {
        return $description.': '.$value;
    }
}
