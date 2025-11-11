<?php

namespace app\helpers;

/**
 * IpHelper provides IP address masking utilities
 * 
 * Supports both IPv4 and IPv6 addresses with privacy-preserving partial masking
 */
class IpHelper
{
    /**
     * Mask an IP address for privacy
     * 
     * - IPv4: Masks last 2 octets (e.g., 46.211.**.** )
     * - IPv6: Masks last 4 sections (e.g., 2001:0db8:11a3:09d7:****:****:****:****)
     * 
     * @param string $ip The IP address to mask
     * @return string The masked IP address
     */
    public static function mask($ip)
    {
        if (empty($ip)) {
            return '';
        }

        // Detect IP version
        if (self::isIPv4($ip)) {
            return self::maskIPv4($ip);
        } elseif (self::isIPv6($ip)) {
            return self::maskIPv6($ip);
        }

        // If we can't determine the type, return as-is (should not happen)
        return $ip;
    }

    /**
     * Check if an IP address is IPv4
     * 
     * @param string $ip
     * @return bool
     */
    public static function isIPv4($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }

    /**
     * Check if an IP address is IPv6
     * 
     * @param string $ip
     * @return bool
     */
    public static function isIPv6($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    /**
     * Mask an IPv4 address (last 2 octets)
     * 
     * Example: 192.168.1.100 -> 192.168.**.**
     * 
     * @param string $ip
     * @return string
     */
    protected static function maskIPv4($ip)
    {
        $parts = explode('.', $ip);
        
        if (count($parts) === 4) {
            $parts[2] = '**';
            $parts[3] = '**';
            return implode('.', $parts);
        }

        return $ip;
    }

    /**
     * Mask an IPv6 address (last 4 sections)
     * 
     * Example: 2001:0db8:11a3:09d7:1f34:8a2e:07a0:765d -> 2001:0db8:11a3:09d7:****:****:****:****
     * 
     * @param string $ip
     * @return string
     */
    protected static function maskIPv6($ip)
    {
        // Expand compressed IPv6 addresses
        $expanded = self::expandIPv6($ip);
        $parts = explode(':', $expanded);

        if (count($parts) === 8) {
            // Mask last 4 sections
            $parts[4] = '****';
            $parts[5] = '****';
            $parts[6] = '****';
            $parts[7] = '****';
            return implode(':', $parts);
        }

        return $ip;
    }

    /**
     * Expand a compressed IPv6 address to full format
     * 
     * Example: 2001:db8::1 -> 2001:0db8:0000:0000:0000:0000:0000:0001
     * 
     * @param string $ip
     * @return string
     */
    protected static function expandIPv6($ip)
    {
        // Handle :: compression
        if (strpos($ip, '::') !== false) {
            $parts = explode('::', $ip);
            $left = !empty($parts[0]) ? explode(':', $parts[0]) : [];
            $right = !empty($parts[1]) ? explode(':', $parts[1]) : [];
            
            $missing = 8 - (count($left) + count($right));
            $middle = array_fill(0, $missing, '0000');
            
            $all = array_merge($left, $middle, $right);
        } else {
            $all = explode(':', $ip);
        }

        // Pad each section to 4 characters
        $all = array_map(function($part) {
            return str_pad($part, 4, '0', STR_PAD_LEFT);
        }, $all);

        return implode(':', $all);
    }

    /**
     * Get the original IP from request, considering proxy headers
     * 
     * @return string
     */
    public static function getUserIP()
    {
        $ip = null;

        // Check for proxy headers
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        // Validate the IP
        if ($ip && (self::isIPv4($ip) || self::isIPv6($ip))) {
            return $ip;
        }

        return '0.0.0.0'; // Fallback
    }
}
