<?php
/**
 * View Helper
 * Utility functions for template rendering
 * 
 * @package    nicsrs_ssl_admin
 * @author     HVN GROUP
 * @copyright  Copyright (c) HVN GROUP (https://hvn.vn)
 */

namespace NicsrsAdmin\Helper;

class ViewHelper
{
    /**
     * Format date for display
     * 
     * @param string|null $date Date string
     * @param string $format Output format
     * @return string Formatted date
     */
    public function formatDate(?string $date, string $format = 'Y-m-d'): string
    {
        if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
            return '-';
        }
        
        $timestamp = strtotime($date);
        return $timestamp ? date($format, $timestamp) : '-';
    }

    /**
     * Format date with time
     * 
     * @param string|null $date Date string
     * @return string Formatted datetime
     */
    public function formatDateTime(?string $date): string
    {
        return $this->formatDate($date, 'Y-m-d H:i');
    }

    /**
     * Get time ago string
     * 
     * @param string $date Date string
     * @return string Time ago string
     */
    public function timeAgo(string $date): string
    {
        $timestamp = strtotime($date);
        if (!$timestamp) {
            return '-';
        }

        $diff = time() - $timestamp;
        
        if ($diff < 60) {
            return 'Just now';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return $mins . ' min' . ($mins > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } else {
            return $this->formatDate($date);
        }
    }

    /**
     * Get status badge HTML
     * 
     * @param string $status Status code
     * @return string HTML badge
     */
    public function statusBadge(string $status): string
    {
        $status = strtolower($status);
        
        $badges = [
            'complete' => ['class' => 'success', 'label' => 'Complete'],
            'issued' => ['class' => 'success', 'label' => 'Issued'],
            'active' => ['class' => 'success', 'label' => 'Active'],
            'pending' => ['class' => 'warning', 'label' => 'Pending'],
            'processing' => ['class' => 'warning', 'label' => 'Processing'],
            'awaiting' => ['class' => 'default', 'label' => 'Awaiting'],
            'draft' => ['class' => 'info', 'label' => 'Draft'],
            'cancelled' => ['class' => 'danger', 'label' => 'Cancelled'],
            'revoked' => ['class' => 'danger', 'label' => 'Revoked'],
            'expired' => ['class' => 'danger', 'label' => 'Expired'],
        ];

        $badge = isset($badges[$status]) ? $badges[$status] : ['class' => 'default', 'label' => ucfirst($status)];
        
        return sprintf(
            '<span class="label label-%s">%s</span>',
            htmlspecialchars($badge['class']),
            htmlspecialchars($badge['label'])
        );
    }

    /**
     * Get validation type badge HTML
     * 
     * @param string $type Validation type (dv, ov, ev)
     * @return string HTML badge
     */
    public function validationBadge(string $type): string
    {
        $type = strtoupper($type);
        
        $badges = [
            'DV' => ['class' => 'primary', 'label' => 'DV'],
            'OV' => ['class' => 'info', 'label' => 'OV'],
            'EV' => ['class' => 'success', 'label' => 'EV'],
        ];

        $badge = isset($badges[$type]) ? $badges[$type] : ['class' => 'default', 'label' => $type];
        
        return sprintf(
            '<span class="label label-%s">%s</span>',
            htmlspecialchars($badge['class']),
            htmlspecialchars($badge['label'])
        );
    }

    /**
     * Format price for display
     * 
     * @param float|null $price Price value
     * @param string $currency Currency symbol
     * @return string Formatted price
     */
    public function formatPrice(?float $price, string $currency = '$'): string
    {
        if ($price === null) {
            return '-';
        }
        
        return $currency . number_format($price, 2);
    }

    /**
     * Render Yes/No icon
     * 
     * @param bool $value Boolean value
     * @return string HTML icon
     */
    public function yesNoIcon(bool $value): string
    {
        if ($value) {
            return '<i class="fa fa-check text-success"></i>';
        }
        return '<i class="fa fa-times text-muted"></i>';
    }

    /**
     * Truncate string with ellipsis
     * 
     * @param string $string Input string
     * @param int $length Max length
     * @return string Truncated string
     */
    public function truncate(string $string, int $length = 50): string
    {
        if (strlen($string) <= $length) {
            return htmlspecialchars($string);
        }
        
        return htmlspecialchars(substr($string, 0, $length)) . '...';
    }

    /**
     * Calculate days until date
     * 
     * @param string $date Target date
     * @return int|null Days (negative if past)
     */
    public function daysUntil(string $date): ?int
    {
        $timestamp = strtotime($date);
        if (!$timestamp) {
            return null;
        }
        
        return (int) ceil(($timestamp - time()) / 86400);
    }

    /**
     * Get days left badge
     * 
     * @param string $endDate End date
     * @return string HTML badge
     */
    public function daysLeftBadge(string $endDate): string
    {
        $days = $this->daysUntil($endDate);
        
        if ($days === null) {
            return '-';
        }
        
        if ($days < 0) {
            return '<span class="label label-danger">Expired</span>';
        }
        
        if ($days <= 7) {
            $class = 'danger';
        } elseif ($days <= 30) {
            $class = 'warning';
        } else {
            $class = 'success';
        }
        
        return sprintf('<span class="label label-%s">%d days</span>', $class, $days);
    }

    /**
     * Escape HTML output
     * 
     * @param string|null $string Input string
     * @return string Escaped string
     */
    public function e(?string $string): string
    {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}