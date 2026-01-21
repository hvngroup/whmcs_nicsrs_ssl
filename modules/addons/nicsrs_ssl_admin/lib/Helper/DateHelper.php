<?php
/**
 * Date Helper Class
 * Handles date parsing and formatting for NicSRS API responses
 * API returns dates in format: Y-m-d H:i:s (e.g., 2026-01-19 08:00:00)
 * 
 * @package    nicsrs_ssl_admin
 * @author     HVN GROUP
 * @copyright  Copyright (c) HVN GROUP (https://hvn.vn)
 */

namespace NicsrsAdmin\Helper;

class DateHelper
{
    /**
     * Parse datetime from API response
     * API format: "2026-01-19 08:00:00"
     * 
     * @param string|null $value Date string from API
     * @return string|null Formatted datetime or null
     */
    public static function parseDateTime(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }
        
        $ts = strtotime($value);
        if ($ts === false) {
            return null;
        }
        
        return date('Y-m-d H:i:s', $ts);
    }

    /**
     * Parse date only (for dueDate, etc.)
     * API format: "2028-01-19 00:00:00"
     * 
     * @param string|null $value Date string from API
     * @return string|null Date only (Y-m-d) or null
     */
    public static function parseDate(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }
        
        $ts = strtotime($value);
        if ($ts === false) {
            return null;
        }
        
        return date('Y-m-d', $ts);
    }

    /**
     * Format datetime for display
     * 
     * @param string|null $datetime Date/datetime string
     * @param string $format Output format
     * @return string Formatted date or 'N/A'
     */
    public static function formatDisplay(?string $datetime, string $format = 'M j, Y H:i'): string
    {
        if (empty($datetime) || !self::isValidDate($datetime)) {
            return 'N/A';
        }
        
        $ts = strtotime($datetime);
        if ($ts === false) {
            return 'N/A';
        }
        
        return date($format, $ts);
    }

    /**
     * Format date only for display (no time)
     * 
     * @param string|null $date Date string
     * @param string $format Output format
     * @return string Formatted date or 'N/A'
     */
    public static function formatDateOnly(?string $date, string $format = 'M j, Y'): string
    {
        if (empty($date) || !self::isValidDate($date)) {
            return 'N/A';
        }
        
        $ts = strtotime($date);
        if ($ts === false) {
            return 'N/A';
        }
        
        return date($format, $ts);
    }

    /**
     * Check if date is valid (not empty, not 0000-00-00)
     * 
     * @param string|null $date Date to check
     * @return bool
     */
    public static function isValidDate(?string $date): bool
    {
        if (empty($date)) {
            return false;
        }
        
        // Check for invalid date patterns
        $invalidPatterns = [
            '0000-00-00',
            '0000-00-00 00:00:00',
            '1970-01-01 00:00:00',
            '1970-01-01',
        ];
        
        if (in_array($date, $invalidPatterns)) {
            return false;
        }
        
        // Verify it parses to a valid timestamp
        $ts = strtotime($date);
        return $ts !== false && $ts > 0;
    }

    /**
     * Calculate days remaining until a date
     * 
     * @param string|null $endDate End date
     * @return int|null Days remaining or null if invalid
     */
    public static function daysRemaining(?string $endDate): ?int
    {
        if (!self::isValidDate($endDate)) {
            return null;
        }
        
        $end = strtotime($endDate);
        $now = strtotime('today');
        
        if ($end === false) {
            return null;
        }
        
        $diff = $end - $now;
        return (int) floor($diff / 86400);
    }

    /**
     * Get days remaining badge HTML
     * 
     * @param string|null $endDate End date
     * @return string HTML badge
     */
    public static function daysRemainingBadge(?string $endDate): string
    {
        $days = self::daysRemaining($endDate);
        
        if ($days === null) {
            return '';
        }
        
        if ($days < 0) {
            return '<span class="label label-danger"><i class="fa fa-exclamation-triangle"></i> Expired</span>';
        }
        
        if ($days === 0) {
            return '<span class="label label-danger"><i class="fa fa-exclamation-triangle"></i> Expires Today</span>';
        }
        
        if ($days <= 7) {
            return sprintf(
                '<span class="label label-danger"><i class="fa fa-exclamation-triangle"></i> %d day%s left</span>',
                $days,
                $days === 1 ? '' : 's'
            );
        }
        
        if ($days <= 30) {
            return sprintf(
                '<span class="label label-warning"><i class="fa fa-clock-o"></i> %d days left</span>',
                $days
            );
        }
        
        if ($days <= 90) {
            return sprintf(
                '<span class="label label-info">%d days left</span>',
                $days
            );
        }
        
        return sprintf(
            '<span class="label label-success">%d days left</span>',
            $days
        );
    }

    /**
     * Check if certificate is expiring soon (within 30 days)
     * 
     * @param string|null $endDate End date
     * @return bool
     */
    public static function isExpiringSoon(?string $endDate): bool
    {
        $days = self::daysRemaining($endDate);
        return $days !== null && $days >= 0 && $days <= 30;
    }

    /**
     * Check if certificate is expired
     * 
     * @param string|null $endDate End date
     * @return bool
     */
    public static function isExpired(?string $endDate): bool
    {
        $days = self::daysRemaining($endDate);
        return $days !== null && $days < 0;
    }

    /**
     * Get relative time string (e.g., "5 minutes ago")
     * 
     * @param string|null $datetime Datetime string
     * @return string Relative time or formatted date
     */
    public static function relativeTime(?string $datetime): string
    {
        if (!self::isValidDate($datetime)) {
            return 'N/A';
        }
        
        $ts = strtotime($datetime);
        $diff = time() - $ts;
        
        if ($diff < 0) {
            // Future date
            return self::formatDisplay($datetime);
        }
        
        if ($diff < 60) {
            return 'Just now';
        }
        
        if ($diff < 3600) {
            $mins = floor($diff / 60);
            return $mins . ' minute' . ($mins === 1 ? '' : 's') . ' ago';
        }
        
        if ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours === 1 ? '' : 's') . ' ago';
        }
        
        if ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days === 1 ? '' : 's') . ' ago';
        }
        
        return self::formatDisplay($datetime, 'M j, Y');
    }

    /**
     * Convert datetime to ISO 8601 format
     * 
     * @param string|null $datetime Datetime string
     * @return string|null ISO 8601 formatted string or null
     */
    public static function toIso8601(?string $datetime): ?string
    {
        if (!self::isValidDate($datetime)) {
            return null;
        }
        
        $ts = strtotime($datetime);
        return date('c', $ts);
    }

    /**
     * Get the current timestamp in database format
     * 
     * @return string Current datetime (Y-m-d H:i:s)
     */
    public static function now(): string
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * Get current date only
     * 
     * @return string Current date (Y-m-d)
     */
    public static function today(): string
    {
        return date('Y-m-d');
    }
}