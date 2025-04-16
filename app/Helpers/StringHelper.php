<?php

namespace App\Helpers;

use Illuminate\Support\Str;
use Pdp\Domain;
use Pdp\TopLevelDomains;

class StringHelper
{
    public static function cleanHtml(string $htmlContent): string
    {
        return str_replace([PHP_EOL, "\r", "\t", "\n"], '', strip_tags(html_entity_decode($htmlContent)));
    }// str_replace(PHP_EOL, '', $str);[, "\n", ]


    public static function removeScripts(string $htmlContent): string
    {
        return preg_replace(
            '/\s+/',
            ' ',
            preg_replace(
                '#<!--(.*?)-->#is',
                '',
                preg_replace(
                    '#<link(.*?)/>#is',
                    '',
                    preg_replace(
                        '#<i(.*?)>(.*?)</i>#is',
                        '',
                        preg_replace(
                            '#<svg(.*?)>(.*?)</svg>#is',
                            '',
                            preg_replace(
                                '#<style(.*?)>(.*?)</style>#is',
                                '',
                                preg_replace(
                                    '#<noscript(.*?)>(.*?)</noscript>#is',
                                    '',
                                    preg_replace(
                                        '#<head(.*?)>(.*?)</head>#is',
                                        '',
                                        preg_replace(
                                            '#<script(.*?)>(.*?)</script>#is',
                                            '',
                                            str_replace([PHP_EOL, "\r", "\t", "\n"], '', $htmlContent)
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            )
        );
    }

    public static function isInteger(mixed $value): bool
    {
        return (!is_int($value) ? (ctype_digit($value)) : true);
    }

    public static function getDomain(string $url): string
    {
        try {
            $topLevelDomains = TopLevelDomains::fromPath(storage_path('app/data/tlds-alpha-by-domain.txt'));
            $domain = Domain::fromIDNA2008(parse_url($url, PHP_URL_HOST));
            $result = $topLevelDomains->resolve($domain);
            return $result->registrableDomain()->toString();
        } catch (\Exception $exception) {
        }

        return parse_url($url, PHP_URL_HOST);
    }

    public static function removeAccessibility(string $string): string
    {
        return Str::of($string)->replace([
                                          "Accessibility Mode",
                                          "Epilepsy Safe Mode",
                                          "Dampens color and removes blinks to eliminate the risk of seizures caused by flashing or blinking animations and risky color combinations.",
                                          "Visually Impaired Mode",
                                          "Adjusts the website's visuals for users with visual impairments such as Degrading Eyesight, Tunnel Vision, Cataract, Glaucoma, and others.",
                                          "Cognitive Disability Mode",
                                          "Provides different assistive options to help users with cognitive impairments such as Dyslexia, Autism, CVA, and others, to focus on essential website elements more easily.",
                                          "ADHD Friendly Mode",
                                          "Reduces distractions and improves focus for users with ADHD and Neurodevelopmental disorders, helping them read, browse, and focus on main website elements more easily.",
                                          "Blindness Mode",
                                          "Configures the website to be compatible with screen-readers such as JAWS, NVDA, VoiceOver, and TalkBack for blind users.",
                                          "Readable Experience",
                                          "Enhances text readability by providing options like Content Scaling, Text Magnifier, Readable Font, Dyslexia Friendly features, Highlight Titles, and Highlight Links.",
                                          "Visually Pleasing Experience",
                                          "Adjusts the visual experience with options like Dark Contrast, Light Contrast, Monochrome, High Contrast, High Saturation, and Low Saturation.",
                                          "Easy Orientation",
                                          "Provides features like Mute Sounds, Hide Images, Virtual Keyboard, Reading Guide, Stop Animations, Reading Mask, Highlight Hover, Highlight Focus, Big Dark Cursor, Big Light Cursor, and Navigation Keys.",
                                          "A screen-reader is software for blind users that is installed on a computer and smartphone, and websites must be compatible with it.",
                                          "Default",
                                          "Font",
                                          "Readable",
                                          "Content Scaling",
                                          "Text Magnifier",
                                          "Readable Font",
                                          "Dyslexia Friendly",
                                          "Highlight Titles",
                                          "Highlight Links",
                                          " Font Sizing",
                                          "Line Height",
                                          "Letter Spacing",
                                          "Left Aligned",
                                          "Center Aligned",
                                          "Right Aligned",
                                          " Visually Pleasing",
                                          "Dark Contrast",
                                          "Light Contrast",
                                          "Monochrome",
                                          "High Contrast",
                                          "High Saturation",
                                          "Low Saturation",
                                          " Adjust Text Colors",
                                          "Adjust Title Colors",
                                          "Adjust Background Colors",
                                          "Easy Orientation",
                                          "Mute Sounds",
                                          "Our Awards",
                                          "Download Form",
                                          "Hide Forever",
                                          "Share on",
                                          "Hide Images",
                                          "Virtual Keyboard",
                                          "Reading Guide",
                                          "Stop Animations",
                                          "Reading Mask",
                                          "Highlight Hover",
                                          "Highlight Focus",
                                          "Big Dark Cursor",
                                          "Big Light Cursor",
                                          "Navigation Keys",
                                          "Link navigator",
                                          "Facebook",
                                          "Instagram",
                                          "Twitter",
                                          "Visitors",
                                          "Loan Calculator",
                                          "Downloads",
                                          "Tenders",
                                          "Members Portal",
                                          "Careers Portal",
                                          "Tariff Guide",
                                          "Privacy Policy",
                                          "Copyright",
                                          " Privacy Policy",
                                          "Wymore",
                                          "Reset Settings",
                                         ], '')->squish()->toString();
    }
}
