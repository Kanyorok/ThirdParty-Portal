<?php

if (!function_exists('appDate')) {
    /**
     * Format a date using the application's date format.
     *
     * @param \DateTimeInterface|string|null $date
     * @param string|null $format
     * @return string
     */
    function appDate($date, ?string $format = null): string
    {
        if (is_null($date)) {
            return '';
        }

        // Convert string to Carbon instance if needed
        if (is_string($date)) {
            try {
                $date = \Carbon\Carbon::parse($date);
            } catch (\Exception $e) {
                return $date;
            }
        }

        // Use provided format or default application format
        $format = $format ?? config('app.date_format', 'M d, Y H:i');

        // Format the date
        if ($date instanceof \DateTimeInterface) {
            return $date->format($format);
        }

        return (string) $date;
    }
}
